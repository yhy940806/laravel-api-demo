<?php

namespace App\Services\Common;

use Log;
use Util;
use Constant;
use Exception;
use ZipArchive;
use ArrayObject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use App\Events\Soundblock\OnHistory;
use Illuminate\Support\Facades\Storage;
use App\Facades\Soundblock\Accounting\Charge;
use App\Models\Soundblock\{Collection, File, Project};
use League\Flysystem\{FileNotFoundException, UnreadableFileException};
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use App\Repositories\{Common\AppRepository, Soundblock\CollectionRepository, Soundblock\FileRepository};

class ZipService {
    protected $ffprobe;

    protected $ProjectImage = "artwork.png";

    protected $availableExtensions = [
        "(jpg)", "(bmp)", "(jpeg)", "(gif)",
    ];
    protected $track = 0;
    protected $fileRepo;
    protected $colRepo;
    /**
     * @var AppRepository
     */
    private AppRepository $appRepository;
    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    private \Illuminate\Filesystem\FilesystemAdapter $soundblockAdapter;
    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    private \Illuminate\Filesystem\FilesystemAdapter $localAdapter;
    /**
     * @param FileRepository $fileRepo
     * @param CollectionRepository $colRepo
     * @param AppRepository $appRepository
     */
    public function __construct(FileRepository $fileRepo, CollectionRepository $colRepo, AppRepository $appRepository) {
        $this->appRepository = $appRepository;
        $this->fileRepo = $fileRepo;
        $this->colRepo = $colRepo;
        $this->track = 0;
        $this->initFileSystemAdatper();
    }

    /**
     * @return void
     */
    private function initFileSystemAdatper() {
        if (env("APP_ENV") == "local") {
            $this->soundblockAdapter = Storage::disk("local");
        } else {
            $this->soundblockAdapter = Storage::disk("s3-soundblock");
        }
        $this->localAdapter = Storage::disk("local");
    }

    /**
     * Get the path of file uploaded.
     * @param UploadedFile $file
     * @param string $name
     * @param string $path
     *
     * @return string|bool $path
     */
    public function putFile(UploadedFile $file, string $name, ?string $path = null): ?string {
        if (is_null($path)) {
            $path = Util::upload_path();
        }

        if ($this->soundblockAdapter->exists($path . Constant::Separator . $name)) {
            $this->soundblockAdapter->delete($path . Constant::Separator . $name);
        }
        $storagePath = $this->soundblockAdapter->putFileAs($path, $file, $name);

        return ($storagePath !== false ? $name : null);
    }

    /**
     * @param string $src
     * @param string $dest
     *
     * @return bool
     */
    public function moveFile($src, $dest): bool {
        if ($this->soundblockAdapter->exists($src)) {
            $this->soundblockAdapter->move($src, $dest);
            return(true);
        } else {
            return(false);
        }
    }

    /**
     * @param string $path
     * @param UploadedFile $objFile
     * @param bool $bnChg
     * @return string
     */
    public function upload(string $path, UploadedFile $objFile, bool $bnChg = false): string {
        $path = $this->saveFile($path, $objFile, $bnChg);

        return ($this->soundblockAdapter->url($path));
    }

    /**
     * @param string $path
     * @param UploadedFile $objFile
     * @param bool $bnChg
     * @return string
     */
    public function saveFile(string $path, UploadedFile $objFile, bool $bnChg = false): string {
        if ($this->soundblockAdapter->exists($path)) {
            $this->soundblockAdapter->delete($path);
        }

        if ($bnChg) {
            $fileName = Util::random_str() . "." . $objFile->getClientOriginalExtension();
            $this->soundblockAdapter->putFileAs($path, $objFile, $fileName);
        } else {
            $fileName = $objFile->getClientOriginalName();
            $this->soundblockAdapter->putFileAs($path, $objFile, $objFile->getClientOriginalName());
        }

        return($path . Constant::Separator . $fileName);
    }

    public function putArtwork(Project $objProject, $objFile) {
        $artworkPath = Util::project_path($objProject);

        return($this->saveAvatar($artworkPath, $objFile));
    }

    /**
     * @param string $path
     * @param UploadedFile $uploadedFile
     * @param string $saveName
     * @return string|null
     */
    public function saveAvatar(string $path, UploadedFile $uploadedFile, string $saveName = null): ?string {
        if ($this->soundblockAdapter->exists($path)) {
            $this->soundblockAdapter->delete($path);
        }
        $ext = $uploadedFile->getClientOriginalExtension();

        if (Util::lowerLabel($ext) === "png") {
            if ($saveName) {
                $fileName = $saveName . ".png";
            } else {
                $fileName = config("constant.soundblock.project_avatar");
            }
            if ($this->soundblockAdapter->putFileAs($path, $uploadedFile, $fileName))
                return($fileName);
        } else {
            // convert the other image type to png.
        }

        return(null);
    }

    /**
     * @param Project $project
     * @param string $url
     * @throws FileNotFoundException
     *
     * @return string
     */
    public function moveArtwork(Project $project, string $artwork): string {
        $srcPath = Util::draft_artwork_path($artwork);
        if (!$this->soundblockAdapter->exists($srcPath)) {
            throw new FileNotFoundException($srcPath);
        }
        $destPath = Util::artwork_path($project);
        if ($this->soundblockAdapter->exists($destPath)) {
            $this->soundblockAdapter->delete($destPath);
        }
        $this->soundblockAdapter->move($srcPath, $destPath);

        return($destPath);
    }

    /**
     * @param \Illuminate\Http\UploadedFile $uploadedFile
     *
     * @return string
     */
    public function putDraftArtwork($uploadedFile) {
        $artworkPath = Util::draft_artwork_path();

        return($this->saveAvatar($artworkPath, $uploadedFile, Util::uuid()));
    }

    /**
     * @param string $fileName
     * @param array $arrFiles
     * @param string $strComment
     * @param Project $project
     * @param User $user
     * @return Collection
     * @throws FileNotFoundException
     */
    public function unzipProject(string $fileName, array $arrFiles, string $strComment, Project $project, User $user): Collection {
        if (!$this->soundblockAdapter->exists(Util::uploaded_file_path($fileName))) {
            throw new FileNotFoundException(Util::uploaded_file_path($fileName), 400);
        }

        $extractPath = $this->extract($fileName);
        $collection = $this->handleProjectFiles($extractPath, $arrFiles, $strComment, $project, $user);
        $this->soundblockAdapter->deleteDirectory($extractPath);
        $this->localAdapter->deleteDirectory($extractPath);

        return ($collection);
    }

    /**
     * @param string $fileName
     * @return string
     * @throws Exception
     */
    public function extract(string $fileName): string {
        $extension = Util::file_extension($fileName);

        if ($extension !== "zip") {
            throw new Exception("Not zip file", 400);
        }

        $zip = new ZipArchive();

        $uploadedFilePath = Util::uploaded_file_path($fileName);
        $zipContent = $this->soundblockAdapter->get($uploadedFilePath);
        $this->localAdapter->put($uploadedFilePath, $zipContent);
        $uploadZipFile = $this->localAdapter->path($uploadedFilePath);
        $extractPath = Util::project_extract_path();
        if ($zip->open($uploadZipFile) !== true) {
            throw new UnreadableFileException();
        }
        $zip->extractTo($this->localAdapter->path($extractPath));
        $zip->close();

        // Remove Uploaded Zip File
        $this->localAdapter->delete($uploadedFilePath);
        $this->soundblockAdapter->delete($uploadedFilePath);
        //remove __MACOSX folder
        if ($this->localAdapter->exists($extractPath . Constant::Separator . Constant::__MACOSX)) {
            $this->localAdapter->deleteDirectory($extractPath . Constant::Separator . Constant::__MACOSX);
        }
        $arrLocalFiles = $this->localAdapter->allFiles($extractPath);

        foreach ($arrLocalFiles as $localFile) {
            $this->soundblockAdapter->put($localFile, $this->localAdapter->get($localFile));
        }
        Log::info("extract-path", [$extractPath]);

        return($extractPath);
    }

    /**
     * @param string $extractPath
     * @param array $arrFiles
     * @param string $strComment
     * @param Project $project
     * @param User $user
     * @throws FileNotFoundException
     *
     * @return Collection
     */
    protected function handleProjectFiles(string $extractPath, array $arrFiles, string $strComment, Project $project, User $user): Collection {
        $arrPreSaved = [];
        /** @var \App\Models\Soundblock\Collection */
        $collection = $this->processRefCollection($project, $user, $strComment);
        $arrCollectionFile = new \Illuminate\Database\Eloquent\Collection();
        foreach ($arrFiles as $arrFile) {
            $ext = pathinfo($arrFile["org_file_sortby"], PATHINFO_EXTENSION);

            if ((isset($arrFile["track"]["org_file_sortby"]) || isset($arrFile["track"]["file_uuid"])) && $this->getFileCategory($ext) == "video") {
                if (isset($arrFile["track"]["org_file_sortby"])) {
                    $musicFile = $this->findFileByRelativePath($arrFile["track"]["org_file_sortby"], $arrFiles);
                    if (!is_null($musicFile)) {
                        $arrPreSaved [] = $musicFile;
                        $arrMusicParam = $this->handleFile($extractPath, $musicFile, $project);
                        $music = $this->fileRepo->createInCollection($arrMusicParam, $collection, $user);
                        $arrCollectionFile->push($music);
                        $arrFileParam = $this->handleFile($extractPath, $arrFile, $project, $music);
                    } else {
                        $arrFileParam = $this->handleFile($extractPath, $arrFile, $project);
                    }
                } else {
                    $music = $this->fileRepo->find($arrFile["track"]["file_uuid"], true);
                    $arrFileParam = $this->handleFile($extractPath, $arrFile, $project, $music);
                }
            } else {
                if (!is_null($this->findFileByRelativePath($arrFile["org_file_sortby"], $arrPreSaved)))
                    continue;
                $arrFileParam = $this->handleFile($extractPath, $arrFile, $project);
            }
            $file = $this->fileRepo->createInCollection($arrFileParam, $collection, $user);
            $arrCollectionFile->push($file);
        }
        $collection = $this->processRefCollectionFiles($collection, $user, $arrCollectionFile);

        return ($collection);
    }

    /**
     * @param Project $objProject
     * @param string $strComment
     * @return Collection
     * @return Collection
     */
    protected function processRefCollection(Project $objProject, User $user, string $strComment): Collection {
        $latestCollection = $this->colRepo->findLatestByProject($objProject);
        $newCollection = $this->colRepo->create([
            "project_id"                 => $objProject->project_id,
            "project_uuid"               => $objProject->project_uuid,
            "collection_comment"         => $strComment,
            Collection::STAMP_CREATED_BY => $user->user_id,
            Collection::STAMP_UPDATED_BY => $user->user_id,
        ]);
        if ($latestCollection) {
            //attach old resources of old collection.
            $newCollection = $this->colRepo->attachResources($newCollection, $latestCollection, null, null, $user);
        }

        return ($newCollection);
    }

    /**
     * @param string $strExt
     * @return string
     */
    public function getFileCategory(string $strExt): string {
        $strExt = strtolower($strExt);
        if (array_search($strExt, Constant::MusicExtension) !== false) {
            return (Constant::MusicCategory);
        } else if (array_search($strExt, Constant::VideoExtension) !== false) {
            return (Constant::VideoCategory);
        } else if (array_search($strExt, Constant::MerchExtension) !== false) {
            return (Constant::MerchCategory);
        }
        return (Constant::OtherCategory);
    }

    /**
     * @param string $path
     * @param array $arrFiles
     * @return array
     */
    protected function findFileByRelativePath(string $path, array $arrFiles): ?array {
        foreach ($arrFiles as $arrFile) {
            if ($arrFile["org_file_sortby"] == $path)
                return ($arrFile);
        }
        return (null);
    }

    /**
     * @param string $extractPath
     * @param array $arrParam
     * @param Project $project
     * @param File $objMusic
     * @return array
     * @throws FileNotFoundException
     */
    protected function handleFile(string $extractPath, array $arrParam, Project $project, ?File $objMusic = null): ?array {
        $filePath = $extractPath . Constant::Separator . $arrParam["org_file_sortby"];
        $objApp = $this->appRepository->findOneByName("soundblock");

        if (!$this->soundblockAdapter->exists($filePath)) {
            throw new FileNotFoundException($filePath, 417);
        }

        $dest = Util::project_path($project);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileCategory = $this->getFileCategory($extension);
        $physicalName = Util::uuid();
        if (env("APP_ENV") == "local") {
            $md5File = md5_file($this->soundblockAdapter->path($filePath));
        } else {
            $md5File = md5_file($this->soundblockAdapter->temporaryUrl($filePath, now()->addMinute()));
        }
        $this->soundblockAdapter->move($filePath, $dest . Constant::Separator . $physicalName);

        $arrFile = [
            "file_uuid"     => $physicalName,
            "file_name"     => $arrParam["file_name"],
            // TO be fixed.
            "file_path"     => Util::ucfLabel($fileCategory),
            "file_title"    => $arrParam["file_title"],
            // TO be fixed.
            "file_sortby"   => Util::ucfLabel($fileCategory) . Constant::Separator . $arrParam["file_name"],
            "file_category" => $fileCategory,
            "file_size"     => $this->soundblockAdapter->size($dest . Constant::Separator . $physicalName),
            "file_md5"      => $md5File,
        ];

        if ($fileCategory !== Constant::MusicCategory) {
            Charge::chargeService($project->service, "upload", $objApp);
        }

        switch ($fileCategory) {
            case Constant::MusicCategory:
            {
                $this->track++;
                $duration = rand(200, 350);
                if (isset($arrParam["file_track"])) {
                    $arrFile["file_track"] = intval($arrParam["file_track"]);
                    $this->track = $arrParam["file_track"];
                } else {
                    $arrFile["file_track"] = $this->track;
                }
                $arrFile["file_duration"] = $duration;
                break;
            }
            case Constant::VideoCategory:
            {
                if (!is_null($objMusic)) {
                    $arrFile["music_id"] = $objMusic->file_id;
                    $arrFile["music_uuid"] = $objMusic->file_uuid;
                }
                break;
            }
            default:
                break;
        }

        return ($arrFile);
    }

    /**
     * @param Collection $collection
     * @param User $user
     * @param \Illuminate\Database\Eloquent\Collection $arrFile
     *
     * @return Collection
     */
    private function processRefCollectionFiles(Collection $collection, User $user, $arrFile): Collection {
        $historyFiles = $arrFile->map(function ($file) {
            return([
                "new" => $file
            ]);
        });
        event(new OnHistory($collection, "Created", $historyFiles, $user));

        return($collection);
    }

    public function downloadProject(Collection $collection) {
        $zipFilePath = "";

        return ($this->download($zipFilePath));
    }

    /**
     * @param string $path
     * @return mixed
     * @throws FileNotFoundException
     */
    protected function download(string $path) {
        if (!$this->soundblockAdapter->exists($path)) {
            throw new FileNotFoundException($path, 400);
        }

        return ($this->soundblockAdapter->download($path));
    }

    /**
     * @param Collection $collection
     * @return string
     * @throws Exception
     */
    public function zipCollection(Collection $collection): string {
        /** @var \Illuminate\Database\Eloquent\Collection */
        $files = $collection->files;
        /** @var \Illuminate\Database\Eloquent\Collection */
        $directories = $collection->directories;
        $srcPath = Util::project_path($collection->project);

        $arrDirectories = [];
        if ($directories->count() > 0) {
            $arrDirectories = $this->getDirectoryParams($directories);
        }
        $arrFiles = $this->getFileParams($srcPath, $files);
        if (empty($arrDirectories) && empty($arrFiles))
            throw new Exception("Any files or directory not exists on storage.", 417);

        return ($this->zip(Util::project_zip_path(Util::uuid()), $arrFiles, $arrDirectories));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $directories
     * @throws Exception
     *
     * @return array
     */
    protected function getDirectoryParams($directories): array {
        $collectionDirectories = collect();
        $arrDirectories = collect();
        foreach ($directories as $directory) {
            if ($collectionDirectories->isEmpty()) {
                $collectionDirectories->push($directory);
                $arrDirectories->push();
            } else {
                $directoryParam = Util::make_directory_suffix($directory->toArray(), $collectionDirectories);
                $arrDirectories->push($directoryParam);
            }
        }

        return ($arrDirectories->all());
    }

    /**
     * @param string $src
     * @param \Illuminate\Database\Eloquent\Collection $files
     * @throws Exception
     *
     * @return array $arrFiles
     */
    protected function getFileParams(string $src, $files): array {
        $collectionFiles = collect();
        $arrFiles = collect();
        $files = $files->reject(function ($item) use ($src) {
            return (!$this->soundblockAdapter->exists($src . Constant::Separator . $item->file_uuid));
        });

        foreach ($files as $file) {
            if ($collectionFiles->isEmpty()) {
                $collectionFiles->push($file);
                $arrFiles->push($file->toArray());
            } else {
                $param = Util::make_suffix($file->toArray(), $collectionFiles);
                $arrFiles->push($param);
            }

        }
        $arrFiles = $arrFiles->map(function ($item) use ($src) {
            $item = array_merge($item, ["real_path" => $src . Constant::Separator . $item["file_uuid"]]);
            return ($item);
        });

        return ($arrFiles->all());
    }

    /**
     * @param string $zipPath
     * @param array $arrFiles
     * @param array|null $arrDirectories
     * @return string
     */
    public function zip(string $zipPath, array $arrFiles, ?array $arrDirectories = null): ?string {
        $zip = new ZipArchive();
        if ($this->soundblockAdapter->exists($zipPath)) {
            $this->soundblockAdapter->delete($zipPath);
        }

        if ($zip->open($this->soundblockAdapter->path($zipPath), ZipArchive::CREATE)) {
            if (!is_null($arrDirectories)) {
                //Add empty directory
                foreach ($arrDirectories as $directory) {
                    $zip->addEmptyDir($directory["directory_sortby"]);
                }
            }
            foreach ($arrFiles as $file) {
                if ($this->soundblockAdapter->exists($file["real_path"])) {
                    $zip->addFile($this->soundblockAdapter->path($file["real_path"]), $file["file_sortby"]);
                }
            }
            $res = $zip->close();

            if ($res === true) {
                return ($zipPath);
            } else {
                throw new CannotWriteFileException();
            }
        } else {
            throw new CannotWriteFileException();
        }
    }

    /**
     * @param Collection $collection
     * @param \Illuminate\Database\Eloquent\Collection $files
     * @return string
     *
     * @throws FileNotFoundException
     */
    public function zipFiles(Collection $collection, $files): string {
        $srcPath = Util::project_path($collection->project);
        $arrFiles = $this->getFileParams($srcPath, $files);

        if (empty($arrFiles)) {
            throw new FileNotFoundException("Any file not exists on storage.", 417);
        }

        return ($this->zip(Util::project_zip_path(Util::uuid()), $arrFiles));
    }

    /**
     * Copy files from "form" to "to"
     * @param array $arrFiles
     * @param string $from
     * @param string $to
     *
     * @return void
     */
    public function copyFiles($arrFiles, $from, $to) {
        if ($this->soundblockAdapter->exists($to)) {
            $this->soundblockAdapter->deleteDirectory($to);
        }
        $this->soundblockAdapter->makeDirectory($to);

        foreach ($arrFiles as $file) {
            $matchedPos = strpos($file, $from);
            $relativePath = substr($file, $matchedPos);
            $this->soundblockAdapter->copy($file, $to . Constant::Separator . $relativePath);
        }
    }

    public function copyFile($file, $dest) {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $newFileBaseName = pathinfo($file, PATHINFO_BASENAME);
        $fileExt = pathinfo($file, PATHINFO_EXTENSION);
        $count = $this->countOfDuplicatedFiles($dest, $fileName, $fileExt);

        if ($count != 0) {
            $newFileBaseName = $fileName . "(" . ($count + 1) . ")" . $fileExt;
        }
        $this->soundblockAdapter->copy($file, $dest . Constant::Separator . $newFileBaseName);
        return ($newFileBaseName);
    }

    /**
     * @param string $strDestDir
     * @param string $strFileName
     * @param string $strExtension
     * @return int
     */
    public function countOfDuplicatedFiles(string $strDestDir, string $strFileName, string $strExtension): int {
        $allFiles = $this->soundblockAdapter->allFiles($strDestDir);
        $count = 0;

        foreach ($allFiles as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (($filename === $strFileName || strpos($filename, $strFileName) !== false) && $extension === $strExtension)
                $count++;
        }

        return ($count);
    }

    /**
     * Rename the name of file if exists already.
     * @param array $arrFiles
     * @return array $arrFiles
     */
    protected function renameIfExists(array $arrFiles): array {
        $count = 0;

        for ($i = 0; $i < count($arrFiles); $i++) {
            $objArray = new ArrayObject($arrFiles);
            $arrTempFiles = $objArray->getArrayCopy();
            array_splice($arrTempFiles, $i, 1);

            for ($j = 0; $j < count($arrTempFiles); $j++) {
                if ($arrFiles[$i]["file_sortby"] == $arrTempFiles[$j]["file_sortby"]) {
                    $count++;
                }
            }
            if ($count > 0) {
                $fileName = pathinfo($arrFiles[$i]["file_name"], PATHINFO_FILENAME);
                $fileExt = pathinfo($arrFiles[$i]["file_name"], PATHINFO_EXTENSION);
                $arrFiles[$i]["file_sortby"] = $arrFiles[$i]["file_path"] . Constant::Separator . $fileName . "(" . $count . ")." . $fileExt;

                return ($this->renameIfExists($arrFiles));
            }
        }

        return ($arrFiles);
    }
}
