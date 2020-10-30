<?php

namespace App\Repositories\Soundblock;

use Util;
use Auth;
use App\Models\{BaseModel, User};
use App\Repositories\BaseRepository;
use App\Models\Soundblock\{Collection, File, Project};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionRepository extends BaseRepository {

    public function __construct(Collection $objCol) {
        $this->model = $objCol;
    }

    public function findAllByProject(Project $objProject, string $type = "soundblock", ?int $perPage = null) {
        /** @var \Illuminate\Database\Eloquent\Builder */
        $queryBuilder = $objProject->collections();
        if ($type == "soundblock") {
            $queryBuilder->with(["history"])->withCount("collectionFilesHistory")->orderBy("collection_id", "desc");
        }

        if ($perPage) {
            $arrCollections = $queryBuilder->paginate($perPage);
        } else {
            $arrCollections = $queryBuilder->get();
        }
        return ($arrCollections);
    }

    /**
     * @param Collection $objCol
     * @return EloquentCollection
     */
    public function getTracks(Collection $objCol) {
        return ($objCol->files()->where("file_category", "music")->get());
    }

    public function getTreeStructure(Collection $objCol) {
        $roots = ["Music", "Video", "Merch", "Other"];
        $results = [];
        foreach ($roots as $root) {
            $category = strtolower($root);
            $results[$category] = $this->makeTree($root, $objCol);
        }
        return ($results);
    }

    protected function makeTree($root = DIRECTORY_SEPARATOR, Collection $objCol, array $tree = null) {
        if (is_null($tree)) {
            $tree = [];
        }

        $arrFiles = $objCol->files()->where("file_path", $root)->get();
        $arrDirs = $objCol->directories()->where("directory_path", $root)->get();

        foreach ($arrFiles as $objFile) {
            array_push($tree, [
                "file_uuid"              => $objFile->file_uuid,
                "file_name"              => $objFile->file_name,
                "file_path"              => $objFile->file_path,
                "file_category"          => $objFile->file_category,
                "file_sortby"            => $objFile->file_sortby,
                "kind"                   => "file",
                BaseModel::STAMP_CREATED => $objFile->stamp_created,
                BaseModel::STAMP_UPDATED => $objFile->stamp_updated,
            ]);
        }

        foreach ($arrDirs as $objDir) {
            array_push($tree, [
                    "directory_uuid"         => $objDir->directory_uuid,
                    "directory_name"         => $objDir->directory_name,
                    "directory_path"         => $objDir->directory_path,
                    "directory_sortby"       => $objDir->directory_sortby,
                    "kind"                   => "directory",
                    BaseModel::STAMP_CREATED => $objDir->stamp_created,
                    BaseModel::STAMP_UPDATED => $objDir->stamp_updated,
                ]
            );
            $childClone = &$tree[count($tree) - 1]["children"];
            $childClone = $this->makeTree($objDir->directory_sortby, $objCol, $childClone);
        }

        return ($tree);
    }

    /**
     * @param Collection $objCol
     * @param array $arrFileUuid
     * @return bool
     */
    public function hasFiles(Collection $collection, $arrFileUuid): bool {
        $collectionFiles = $collection->files()->wherePivotIn("file_uuid", $arrFileUuid)->get();

        return ($collectionFiles->count() == count($arrFileUuid));
    }

    /**
     * @param Collection $objNew
     * @param Collection $objCol
     * @param EloquentCollection $arrDirs
     * @param EloquentCollection $arrFiles
     * @param User $objUser
     */
    public function attachResources(Collection $objNew, Collection $objCol, ?EloquentCollection $arrDirs = null, ?EloquentCollection $arrFiles = null, ?User $objUser = null) {
        if (is_null($arrDirs)) {
            $directories = $objCol->directories;
        } else {
            $directories = $arrDirs;
        }
        if (is_null($arrFiles)) {
            $files = $objCol->files;
        } else {
            $files = $arrFiles;
        }

        if (!$objUser)
            $objUser = Auth::user();

        foreach ($directories as $directory) {
            $objNew->directories()->attach($directory->directory_id, [
                "row_uuid"                  => Util::uuid(),
                "collection_uuid"           => $objNew->collection_uuid,
                "directory_uuid"            => $directory->directory_uuid,
                BaseModel::STAMP_CREATED    => time(),
                BaseModel::STAMP_CREATED_BY => $objUser->user_id,
                BaseModel::STAMP_UPDATED    => time(),
                BaseModel::STAMP_UPDATED_BY => $objUser->user_id,
            ]);
        }

        foreach ($files as $file) {
            $objNew->files()->attach($file->file_id, [
                "row_uuid"                  => Util::uuid(),
                "collection_uuid"           => $objNew->collection_uuid,
                "file_uuid"                 => $file->file_uuid,
                BaseModel::STAMP_CREATED    => time(),
                BaseModel::STAMP_CREATED_BY => $objUser->user_id,
                BaseModel::STAMP_UPDATED    => time(),
                BaseModel::STAMP_UPDATED_BY => $objUser->user_id,
            ]);
        }

        return ($objNew);
    }

    /**
     * @param Collection $collection
     * @param EloquentCollection $directories
     * @param User $user
     * @return Collection
     * @throws \Exception
     */
    public function attachDirectories(Collection $collection, EloquentCollection $directories, ?User $user = null): Collection {
        if (!$user) {
            /** @var User */
            $user = Auth::user();
        }
        foreach ($directories as $directory) {
            $collection->directories()->attach($directory->directory_id, [
                "row_uuid"                  => Util::uuid(),
                "collection_uuid"           => $collection->collection_uuid,
                "directory_uuid"            => $directory->directory_uuid,
                BaseModel::STAMP_CREATED    => time(),
                BaseModel::STAMP_CREATED_BY => $user->user_id,
                BaseModel::STAMP_UPDATED    => time(),
                BaseModel::STAMP_UPDATED_BY => $user->user_id,
            ]);
        }
        return ($collection);
    }

    /**
     * @param Collection $collection
     * @param EloquentCollection $files
     * @param User $user
     * @return Collection
     * @throws \Exception
     */
    public function attachFiles(Collection $collection, EloquentCollection $files, ?User $user = null): Collection {
        if (!$user) {
            /** @var User */
            $user = Auth::user();
        }
        foreach ($files as $file) {
            $collection->files()->attach($file->file_id, [
                "row_uuid"                  => Util::uuid(),
                "collection_uuid"           => $collection->collection_uuid,
                "file_uuid"                 => $file->file_uuid,
                BaseModel::STAMP_CREATED    => time(),
                BaseModel::STAMP_CREATED_BY => $user->user_id,
                BaseModel::STAMP_UPDATED    => time(),
                BaseModel::STAMP_UPDATED_BY => $user->user_id,
            ]);
        }
        return ($collection);
    }

    /**
     * @param Collection $collection
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrderedTracks(Collection $collection) {

        return File::join("soundblock_collections_files", function ($join) use ($collection) {
            $join->on("soundblock_files.file_id", "=", "soundblock_collections_files.file_id")
                 ->where("soundblock_collections_files.collection_id", $collection->collection_id)
                 ->where("soundblock_files.file_category", "music");
        })->join("soundblock_files_music", "soundblock_files.file_id", "=", "soundblock_files_music.file_id")
                   ->orderBy("soundblock_files_music.file_track", "asc")
                   ->get()
                   ->makeHidden(["row_id", "row_uuid", "collection_id", "collection_uuid", BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY, BaseModel::DELETED_AT, BaseModel::CREATED_AT, BaseModel::UPDATED_AT, "directory_uuid"]);
    }

    /**
     * @param Collection $objCollection
     * @param string $path
     *
     * @return Collection
     */
    public function getResources(Collection $objCollection, string $path) {
        /** @var Collection */
        $objLatestCollection = $this->findLatestByProject($objCollection->project);
        $objCollection->load(["files" => function ($query) use ($path) {
            $query->where("file_path", $path);
            $query->orderBy("file_sortby", "asc");
        }, "directories"              => function ($query) use ($path) {
            $query->where("directory_path", $path);
            $query->orderBy("directory_sortby", "asc");
        }]);
        // When file category is music, sortby...
        if (strpos($path, "Music") == 0) {
            $orderedFiles = $objCollection->files->sortBy(function ($file) {
                $meta = $file->meta;
                return (isset($meta["file_track"]) ? intval($meta["file_track"]) : PHP_INT_MAX);
            })->values();
            unset($objCollection->files);
            $objCollection->files = $orderedFiles;
        }
        if ($objLatestCollection->collection_id != $objCollection->collection_id) {
            /** @var \Illuminate\Database\Eloquent\Collection */
            $arrFile = $objCollection->files;
            $arrFile = $arrFile->map(function (File $item, $key) use ($objLatestCollection) {
                /** @var int */
                $flag = Util::getRoot($objLatestCollection, $item);
                if ($flag == 0) {
                    $revertable = false;
                    $restorable = false;
                } else if ($flag == 1) {
                    $revertable = true;
                    $restorable = false;
                } else if ($flag == 2) {
                    $revertable = false;
                    $restorable = true;
                }
                $result = array_merge($item->toArray(), [
                    "restorable" => $restorable,
                    "revertable" => $revertable,
                ]);

                return ($result);
            });
            unset($objCollection->files);
            $objCollection->files = $arrFile;
        }

        return ($objCollection);
    }

    /**
     * @param Project $objProject
     * @return Collection
     */
    public function findLatestByProject(Project $objProject): ?Collection {
        return ($objProject->collections()->orderBy(BaseModel::STAMP_CREATED, "desc")->first());
    }

    /**
     * @param string $collection
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCollectionFilesHistory(string $collection) {
        $arrHiddenField = [
            "file_size", "file_md5", "directory_uuid", Collection::STAMP_CREATED, Collection::STAMP_CREATED_BY,
            Collection::STAMP_UPDATED, Collection::STAMP_UPDATED_BY, "meta",
        ];
        $objCollection = $this->find($collection, true);

        return ($objCollection->collectionFilesHistory->makeHidden($arrHiddenField));
    }
}
