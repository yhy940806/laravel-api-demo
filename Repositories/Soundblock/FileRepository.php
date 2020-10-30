<?php

namespace App\Repositories\Soundblock;

use Util;
use Auth;
use App\Models\{BaseModel, User};
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection as SupportCollection;
use App\Models\Soundblock\{File, Collection, FileMerch, FileMusic, FileOther, FileVideo, ProjectDraft};

class FileRepository extends BaseRepository {

    protected FileMusic $musicModel;
    protected FileVideo $videoModel;
    protected FileMerch $merchModel;
    protected FileOther $otherModel;

    /**
     * @param File $file
     * @param FileMusic $objMusic
     * @param FileVideo $objVideo
     * @param FileMerch $objMerch
     * @param FileOther $objOther
     * @return void
     */
    public function __construct(File $file, FileMusic $objMusic, FileVideo $objVideo, FileMerch $objMerch, FileOther $objOther) {
        $this->model = $file;
        $this->musicModel = $objMusic;
        $this->videoModel = $objVideo;
        $this->merchModel = $objMerch;
        $this->otherModel = $objOther;
    }

    /**
     * @param array $arrWhere
     * @param string $fields
     * @param string $orderBy
     * @return SupportCollection
     * @throws \Exception
     */
    public function findWhere(array $arrWhere, string $fields = "uuid", string $orderBy = null): SupportCollection {
        if ($fields === "uuid") {
            $files = collect($arrWhere)->pluck("file_uuid");
            if (empty($files))
                $files = $arrWhere;
            if ($orderBy) {
                return ($this->model->whereIn("file_uuid", $files)->orderBy($orderBy, "asc")->get());
            } else {
                return ($this->model->whereIn("file_uuid", $files)->get());
            }

        } else if ($fields === "id") {
            if ($orderBy) {
                return ($this->model->whereIn("file_id", $arrWhere)->orderBy($orderBy, "asc")->get());
            } else {
                return ($this->model->whereIn("file_id", $arrWhere)->get());
            }
        } else {
            throw new \Exception();
        }
    }

    /**
     * @param User $objUser
     * @return SupportCollection
     */
    public function findNoConfirmed(User $objUser): SupportCollection {
        return ($this->model->whereNull("file_path")
                            ->where(File::STAMP_CREATED_BY, $objUser->user_id)->get());
    }

    /**
     * @param array $arrFile
     * @param Collection $collection
     * @param User $user
     * @return File
     * @throws \Exception
     */
    public function createInCollection(array $arrFile, Collection $collection, ?User $user = null) {
        if (is_null($user)) {
            if (!Auth::user()) {
                throw new \Exception("Invalid Parameter");
            }
            /** @var User */
            $user = Auth::user();
        }

        $arrFile = Util::rename_file($collection, $arrFile);
        $model = $this->createModel($arrFile, $user);
        $model->collections()->attach($collection->collection_id, [
            "row_uuid"                  => Util::uuid(),
            "collection_uuid"           => $collection->collection_uuid,
            "file_uuid"                 => $model->file_uuid,
            BaseModel::STAMP_CREATED    => time(),
            BaseModel::STAMP_UPDATED    => time(),
            BaseModel::STAMP_CREATED_BY => $user->user_id,
            BaseModel::STAMP_UPDATED_BY => $user->user_id,
        ]);

        return ($model);
    }

    /**
     * @param array $arrParams
     * @param User $user
     * @return File
     * @throws \Exception
     */
    public function createModel(array $arrParams, User $user) {
        $arrFile = [];
        if (!isset($arrParams["file_category"]))
            throw new \Exception("Invalid parameter", 417);
        $model = new File;
        $arrParams[File::STAMP_CREATED_BY] = $user->user_id;
        $arrParams[File::STAMP_UPDATED_BY] = $user->user_id;

        if (!isset($arrParams[$model->uuid()])) {
            $arrFile[$model->uuid()] = Util::uuid();
        } else {
            $arrFile[$model->uuid()] = $arrParams[$model->uuid()];
        }

        $arrFile["file_name"] = $arrParams["file_name"];
        $arrFile["file_title"] = $arrParams["file_title"];
        $arrFile["file_path"] = $arrParams["file_path"];
        $arrFile["file_category"] = $arrParams["file_category"];
        $arrFile["file_sortby"] = $arrParams["file_sortby"];
        $arrFile["file_size"] = $arrParams["file_size"];
        $arrFile["file_md5"] = $arrParams["file_md5"];
        $arrFile[File::STAMP_CREATED_BY] = $user->user_id;
        $arrFile[File::STAMP_UPDATED_BY] = $user->user_id;

        $model->fill($arrFile);
        $model->save();

        $fileCategory = Util::lowerLabel($arrParams["file_category"]);
        switch ($fileCategory) {
            case "music":
            {
                $this->insertMusicRecord($model, $arrParams);
                break;
            }
            case "video":
            {
                $this->insertVideoRecord($model, $arrParams);
                break;
            }
            case "merch":
            {
                $this->insertMerchRecord($model, $arrParams);
                break;
            }
            case "other":
            {
                $this->insertOtherRecord($model, $arrParams);
                break;
            }
            default:
                break;
        }

        return ($model);
    }

    public function insertMusicRecord(File $objFile, array $arrFile) {
        $arrMusic = [];
        $model = new FileMusic;

        $arrMusic[$model->uuid()] = Util::uuid();
        $arrMusic["file_id"] = $objFile->file_id;
        $arrMusic["file_uuid"] = $objFile->file_uuid;

        if (is_int($arrFile["file_track"])) {
            $arrMusic["file_track"] = $arrFile["file_track"];
        } else {
            throw new \Exception("File track must be integer.");
        }

        if (isset($arrFile["file_isrc"]))
            $arrMusic["file_isrc"] = $arrFile["file_isrc"];

        $arrMusic["file_duration"] = $arrFile["file_duration"];
        $arrMusic[FileMusic::STAMP_CREATED_BY] = $arrFile[File::STAMP_CREATED_BY];
        $arrMusic[FileMusic::STAMP_UPDATED_BY] = $arrFile[File::STAMP_UPDATED_BY];
        $model->fill($arrMusic);
        $model->save();

        return ($model);
    }

    public function insertVideoRecord(File $objFile, array $arrFile) {
        $arrVideo = [];
        $model = new FileVideo;

        $arrVideo[$model->uuid()] = Util::uuid();
        $arrVideo["file_id"] = $objFile->file_id;
        $arrVideo["file_uuid"] = $objFile->file_uuid;
        if (isset($arrFile["music_id"]) && isset($arrFile["music_uuid"])) {
            $arrVideo["music_id"] = $arrFile["music_id"];
            $arrVideo["music_uuid"] = $arrFile["music_uuid"];
        }
        if (isset($arrFile["file_isrc"])) {
            $arrVideo["file_isrc"] = $arrFile["file_isrc"];
        }

        $arrVideo[FileVideo::STAMP_CREATED_BY] = $arrFile[File::STAMP_CREATED_BY];
        $arrVideo[FileVideo::STAMP_UPDATED_BY] = $arrFile[File::STAMP_UPDATED_BY];

        $model->fill($arrVideo);
        $model->save();

        return ($model);
    }

    public function insertMerchRecord(File $objFile, array $arrFile) {
        $arrMerch = [];
        $model = new FileMerch;

        $arrMerch[$model->uuid()] = Util::uuid();
        $arrMerch["file_id"] = $objFile->file_id;
        $arrMerch["file_uuid"] = $objFile->file_uuid;

        if (isset($arrFile["file_sku"])) {
            $arrMerch["file_sku"] = $arrFile["file_sku"];
        }

        $arrMerch[FileMerch::STAMP_CREATED_BY] = $arrFile[File::STAMP_CREATED_BY];
        $arrMerch[FileMerch::STAMP_UPDATED_BY] = $arrFile[File::STAMP_UPDATED_BY];
        $model->fill($arrMerch);
        $model->save();

        return ($model);
    }

    public function insertOtherRecord(File $objFile, array $arrFile) {
        $arrOther = [];
        $model = new FileOther;

        $arrOther[$model->uuid()] = Util::uuid();
        $arrOther["file_id"] = $objFile->file_id;
        $arrOther["file_uuid"] = $objFile->file_uuid;

        $arrMerch[FileOther::STAMP_CREATED_BY] = $arrFile[File::STAMP_CREATED_BY];
        $arrMerch[FileOther::STAMP_UPDATED_BY] = $arrFile[File::STAMP_UPDATED_BY];
        $model->fill($arrOther);
        $model->save();

        return ($model);
    }

    public function createInDraft(array $arrFile, ProjectDraft $objDraft) {
        $model = $this->create($arrFile);

        $arrDraftJson = $objDraft->draft_json;

        $draftFile = [
            "file_uuid"     => $model->file_uuid,
            "file_name"     => $model->file_name,
            "file_category" => $model->file_category,
        ];

        if ($model->music) {
            $draftFile["file_track"] = $model->music->file_track ? $model->music->file_track : null;
        }

        array_push($arrDraftJson["project"]["project_files"], $draftFile);
        $objDraft->draft_json = $arrDraftJson;
        $objDraft->save();

        return ($objDraft);

    }

    public function create(array $arrParams) {
        $arrFile = [];
        if (!isset($arrParams["file_category"]))
            throw new \Exception("Invalid Parameter", 417);
        $model = $this->model->newInstance();

        if (!isset($arrParams[$model->uuid()])) {
            $arrFile[$model->uuid()] = Util::uuid();
        } else {
            $arrFile[$model->uuid()] = $arrParams[$model->uuid()];
        }

        $arrFile["file_name"] = $arrParams["file_name"];
        $arrFile["file_title"] = $arrParams["file_title"];
        $arrFile["file_path"] = $arrParams["file_path"];
        $arrFile["file_category"] = $arrParams["file_category"];
        $arrFile["file_sortby"] = $arrParams["file_sortby"];
        $arrFile["file_size"] = $arrParams["file_size"];
        $arrFile["file_md5"] = $arrParams["file_md5"];

        $model->fill($arrFile);
        $model->save();

        $fileCategory = Util::lowerLabel($arrParams["file_category"]);
        switch ($fileCategory) {
            case "music":
            {
                $this->insertMusicRecord($model, $arrParams);
                break;
            }
            case "video":
            {
                $this->insertVideoRecord($model, $arrParams);
                break;
            }
            case "merch":
            {
                $this->insertMerchRecord($model, $arrParams);
                break;
            }
            case "other":
            {
                $this->insertOtherRecord($model, $arrParams);
                break;
            }
            default:
                break;
        }

        return ($model);
    }

    public function updateMusicFile(File $objFile, int $intTrack) {
        if ($objFile->music) {
            $musicModel = $objFile->music;
            $arrMusic = [];
            $arrMusic["file_track"] = $intTrack;

            $musicModel->fill($arrMusic);
            $musicModel->save();
        }

        return ($objFile);
    }

    /**
     * @param $model
     * @param array $arrParams
     * @return File
     */
    public function update($model, array $arrParams) {
        $updatableFields = ["file_name", "file_title", "file_path", "file_category", "file_sortby"];
        $arrFile = Util::array_with_key($updatableFields, $arrParams);
        $model->fill($arrFile);
        $model->save();
        $model = $this->updateSub($model, $arrParams);

        return ($model);
    }

    /**
     * @param File $objFile
     * @param array $arrParams
     * @return
     */
    protected function updateSub(File $objFile, array $arrParams): File {
        $category = $objFile->file_category;
        switch ($category) {
            case "music" :
            {
                $updatableFields = ["file_track", "file_isrc"];
                break;
            }
            case "video" :
            {
                $updatableFields = ["music_id", "music_uuid", "file_isrc"];
                break;
            }
            case "merch" :
            {
                $updatableFields = ["file_sku"];
                break;
            }
            case "other" :
                {
                    break;
                }
                break;
        }
        $arrFileSub = Util::array_with_key($updatableFields, $arrParams);

        $objFile->{$category}->fill($arrFileSub);
        $objFile->{$category}->save();
        return ($objFile);
    }

    /**
     * @param File $objFile
     * @return array
     */
    public function getParams(File $objFile): array {
        $arrParams = $objFile->makeHidden([File::STAMP_CREATED, File::STAMP_UPDATED])->toArray();
        $objSubFile = $objFile->{$objFile->file_category}->makeHidden(["row_uuid", BaseModel::STAMP_CREATED, BaseModel::STAMP_UPDATED]);
        if ($objFile->file_category == "video")
            $objSubFile->makeVisible("music_id");

        $arrSubParams = $objSubFile->toArray();
        array_merge($arrParams, $arrSubParams);

        return ($arrParams);
    }

    /**
     * @param Collection
     * @return \Illuminate\Support\Collection
     */
    public function getTracks(Collection $objCol) {
        return ($this->model->join("soundblock_collections_files", "soundblock_files.file_id", "=", "soundblock_collections_files.file_id")
                            ->join("soundblock_files_music", "soundblock_files.file_id", "=", "soundblock_files_music.file_id")
                            ->where("soundblock_collections_files.collection_id", $objCol->collection_id)
                            ->where("soundblock_files.file_category", "music")
                            ->orderBy("soundblock_files_music.file_track", "asc")
                            ->get());
    }

    /**
     * @param string $strSortby
     * @param User $objUser
     * @return
     */
    public function findBySortby(string $strSortby, ?User $objUser = null) {
        if (!$objUser)
            $objUser = Auth::user();
        return ($this->model->where("file_sortby", $strSortby)
                            ->where(File::STAMP_CREATED_BY, $objUser->user_id)
                            ->first());
    }
}
