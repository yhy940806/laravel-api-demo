<?php

namespace App\Helpers;

use App\Models\{Core\Auth\AuthGroup, Core\Auth\AuthModel, Core\App, SupportTicket, User};
use App\Models\Soundblock\{Collection, File, Project, ProjectDraft, ProjectNote, Service, FileHistory};
use App\Services\{UserService, Common\CommonService};
use App\Services\Soundblock\{ProjectDraftService, ProjectNoteService, ProjectService};
use Carbon\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Exception;
use Storage;
use Str;

class Util
{

    const UUID_PATTERN = "/[A-F0-9]{8}\-[A-F0-9]{4}\-4[A-F0-9]{3}\-(8|9|A|B)[A-F0-9]{3}\-[A-F0-9]{12}/";

    public static function user_path($user)
    {
        $objUser = static::user($user);

        return("users" . Constant::Separator . $objUser->user_uuid);
    }

    /**
     * @param mixed $project
     * @throws Exception
     * @return string
     */
    public static function project_path($project) : string
    {
        $objProject = static::project_instance($project);
        $path = "soundblock" . Constant::Separator . "service" . Constant::Separator . $objProject->service->service_uuid
                            . Constant::Separator  . "projects" . Constant::Separator . $objProject->project_uuid;

        return($path);
    }

    /**
     * @param mixed $project
     *
     * @return Project
     */
    private static function project_instance($project) {
        /** @var ProjectService */
        $projectService = app(ProjectService::class);
        if ($project instanceof Project) {
            $objProject = $project;
        } else {
            $objProject = $projectService->find($project);
        }

        return($objProject);
    }

    /**
     * @param mixed $service
     *
     */
    public static function service_path($service)
    {
        $objService = static::service($service);

        $path = "soundblock" . Constant::Separator . "service" . Constant::Separator . $objService->service_uuid;

        return($path);
    }

    /**
     * @param mixed $user
     * @throws Exception
     * @return User
     */
    protected static function user($user) : User
    {
        /** @var UserService */
        $userService = app(UserService::class);
        if ($user instanceof User) {
            $objUser = $user;
        } else {
            $objUser = $userService->find($user, true);
        }

        return($objUser);
    }

    /**
     * @param mixed $service
     * @return Service
     */
    protected static function service($service) : Service
    {
        /** @var CommonService */
        $commonService = app(CommonService::class);
        if ($service instanceof Service)
        {
            $objService = $service;
        } else {
            $objService = $commonService->find($service);
        }

        return($objService);
    }

    /**
     * @param mixed $note
     * @return ProjectNote
     */
    protected static function project_note($note) : ProjectNote
    {
        /** @var ProjectNoteService */
        $noteService = app(ProjectNoteService::class);
        if ($note instanceof ProjectNote)
        {
            $objNote = $note;
        } else if (is_int($note)) {
            $objNote = $noteService->find($note);
        }

        return($objNote);
    }

    /**
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    private static function get_adapter() {
        if (env("APP_ENV") == "local") {
            /** @var \Illuminate\Filesystem\FilesystemAdapter */
            $fileAdapter = Storage::disk("local");
        } else {
            /** @var \Illuminate\Filesystem\FilesystemAdapter */
            $fileAdapter = Storage::disk("s3-soundblock");
        }

        return($fileAdapter);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function draft_artwork(string $url) : ?string
    {
        $uuid = "([A-F0-9]{8}\-[A-F0-9]{4}\-4[A-F0-9]{3}\-(8|9|A|B)[A-F0-9]{3}\-[A-F0-9]{12})";
        /**
         * debug
         */
        // $pattern = "#" . URL::to("/storage/soundblock/service") . "/" . $uuid . "/projects/drafts/artworks/" . $uuid . "\.png#";
        $pattern = "#" . env("APP_URL") . "/storage/soundblock/service" . "/" . $uuid . "/projects/drafts/artworks/" . $uuid . "\.png#";
        preg_match($pattern, $url, $matches);

        if ($matches && count($matches) == 5)
        {
            return($matches[3] . ".png");
        } else {
            return(null);
        }
    }

    /**
     * @return string
     */
    public static function download_path() : string
    {
        return(config("constant.soundblock.download_path"));
    }

    /**
     * @return string
     */
    public static function upload_path() : string
    {
        return(config("constant.soundblock.upload_path"));
    }

    /**
     * @param string $fileName
     * @return string
     */
    public static function uploaded_file_path(string $fileName) : string
    {
        return(static::upload_path() . Constant::Separator . $fileName);
    }
    /**
     * @param $uuid
     * @return string
     */
    public static function project_zip_path(string $uuid) : string
    {
        $downloadPath = static::download_path();
        if (!Storage::disk("s3-soundblock")->exists($downloadPath))
            Storage::disk("s3-soundblock")->makeDirectory($downloadPath);

        return($downloadPath . Constant::Separator . "$uuid.zip");
    }

    /**
     * @return string
     */
    public static function project_extract_path() : string
    {
        return(static::upload_path() . Constant::Separator . Util::uuid());
    }

    /**
     * @param mixed $service
     * @return string
     */
    public static function service_note_path($service) : string
    {
        $servicePath = static::service_path($service);

        return($servicePath . Constant::Separator . ".note" . Constant::Separator . static::uuid());
    }

    /**
     * @param mixed $project
     * @return string
     */
    public static function project_note_path($project) : string
    {
        $projectPath = static::project_path($project);

        return($projectPath . Constant::Separator . ".note" . Constant::Separator . static::uuid());
    }

    /**
     * @param mixed $user
     * @return string
     */
    public static function user_note_path($user) : string
    {
        $userPath = static::user_path($user);

        return($userPath . Constant::Separator . ".note" . Constant::Separator . static::uuid());
    }

    /**
     * @param string|null $fileName
     * @return string
     */
    public static function draft_artwork_path(?string $fileName = null) : string
    {
        return is_null($fileName) ? "upload" . Constant::Separator . "artworks" :
                                        "upload" . Constant::Separator . "artworks" . Constant::Separator . $fileName;
    }

    /**
     * @param SupportTicket $ticket
     * @return string
     */
    public static function ticket_path(SupportTicket $ticket) : string
    {
        return("tickets" . Constant::Separator . $ticket->ticket_uuid);
    }

    /**
     * @param mixed $user
     * @return string
     */
    public static function avatar_url($user) : string
    {
        $objUser = static::user($user);
        $path = "public" . Constant::Separator . "users" . Constant::Separator . "avatars" . Constant::Separator . $objUser->user_uuid . ".png";
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $soundblockAdapter = Storage::disk("s3-account");
        if ($soundblockAdapter->exists($path))
        {
            return($path);
        } else {
            return(config("constant.user_avatar"));
        }
    }

    /**
     * @param mixed $project
     * @return string
     */
    public static function artwork_url($project) : string
    {
        $artworkPath = static::artwork_path($project);
        /** @var \Illuminate\Filesystem\FilesystemAdapter */
        $soundblockAdapter = static::get_adapter();
        if ($soundblockAdapter->exists($artworkPath))
        {
            return($soundblockAdapter->url($artworkPath));
        } else {
            return($soundblockAdapter->url(config("constant.project_avatar")));
        }
    }

    /**
     * @param mixed $project
     * @return string
     */
    public static function artwork_path($project) : string
    {
        return("public/" . static::relative_artwork_path($project));
    }

    /**
     * @param mixed $project
     * @return string
     */
    public static function relative_artwork_path($project): string {
        $objProject = static::project_instance($project);
        return ("assets/static/{$objProject->project_uuid}/" . config("constant.soundblock.project_avatar"));
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function deleteFile(string $path) : bool
    {
        if (!Storage::disk("s3-soundblock")->exists($path))
            return(false);
        return(Storage::disk("s3-soundblock")->delete($path));
    }

    /**
     * @param string $needle
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replace(string $needle, string $replace, string $subject) : string
    {

        if (strpos($subject, $needle) !== false)
        {
            $pos = strpos($needle, $subject);
            return($replace . substr($subject, strlen($needle) + $pos));
        } else {
            return($subject);
        }
    }

    public static function is_auth_group($groupName)
    {
        $objAuthGroup = AuthGroup::where("group_name", $groupName)->first();
        if ($objAuthGroup)
        {
            return(true);
        } else {
            return(false);
        }
    }

    /**
     * @param int $intLen
     * @return string
     */
    public static function random_str(int $intLen = 32)
    {
        return(substr(md5(uniqid(mt_rand(), true)), 0, $intLen));
    }

    public static function remember_token()
    {
        return(Str::random(10));
    }

    /**
     * @param string|null $str
     * @return mixed|string|null
     */
    public static function uuid(?string $str = null)
    {
        if (is_null($str))
        {
            return(strtoupper(Str::uuid()));
        } else if (is_string($str) && $str !== ""){
            preg_match(static::UUID_PATTERN, $str, $matches);
            if ($matches && is_array($matches))
                return($matches[0]);
            else return(null);
        } else {
            throw new Exception();
        }
    }

    /**
     * @param string $str
     * @return bool
     */
    public static function is_uuid(string $str) : bool
    {
        return(preg_match(static::UUID_PATTERN, $str));
    }

    public static function now()
    {
        return(Carbon::now()->toDateTimeString());
    }

    /**
     */
    public static function current_time()
    {
        return(Carbon::now()->getTimestamp());
    }

    /**
     * @return string
     */
    public static function today()
    {
        return(Carbon::now()->toDateString());
    }

    /**
     * @param string $strLabel
     * @return string
     */
    public static function ucfLabel(string $strLabel)
    {
        return(ucfirst(strtolower($strLabel)));
    }

    /**
     * @param string $strLabel
     * @param string $delimiter
     * @return string
     */
    public static function ucLabel(string $strLabel, ?string $delimiter = null)
    {
        if (is_null($delimiter))
        {
            return(ucwords(strtolower($strLabel)));
        } else {
            return(ucwords(strtolower($strLabel), $delimiter));
        }

    }

    /**
     * @param string $strLabel
     * @return string
     */
    public static function lowerLabel(string $strLabel)
    {
        return(strtolower($strLabel));
    }

    /**
     * @param string $strLabel
     * @return string
     */
    public static function upperLabel(string $strLabel)
    {
        return(strtoupper($strLabel));
    }

    public static function filterName($names)
    {
        if (is_array($names))
        {
            $result = [];
            foreach($names as $name)
            {
                if (is_string($name))
                {
                    $result []= str_replace("*", "%", $name);
                } else {
                    throw new Exception();
                }
            }

            return($result);
        } else if (is_string($names)){
            return(str_replace("*", "%", $names));
        } else {
            throw new Exception();
        }
    }

    public static function makeGroupName(AuthModel $objAuth, string $groupType, $obj)
    {

        switch (static::lowerLabel($groupType))
        {
            case "service" : {
                return($objAuth->auth_name . "." . static::ucfLabel($groupType) . "." . $obj->service_uuid);
            }

            case "project" : {
                return($objAuth->auth_name . "." . static::ucfLabel($groupType) . "." . $obj->project_uuid);
            }
        }

    }

    public static function makeGroupMemo(AuthModel $objAuth, $groupType, $obj)
    {
        switch (static::lowerLabel($groupType))
        {
            case "service" : {
                return("Soundblock:Service Plan:" . $obj->service_name);
            }

            case "project" : {
                return($objAuth->auth_name . "." . static::ucfLabel($groupType) . ".( " . $obj->project_uuid) . " )";
            }
        }

    }

    /**
     * @param mix $app
     * @return AuthModel
     */
    public static function makeAuth($app)
    {
        if (is_string($app) && static::is_uuid($app))
        {
            $objApp = App::where("app_uuid", $app)->firstOrFail();
        } else if (is_string($app)) {
            $objApp = App::where("app_name", "soundblock")->firstOrFail();
        }
        else if (is_int($app)) {
            $objApp = App::find($app);
        } else if ($app instanceof App)
        {
            $objApp = $app;
        } else {
            throw new Exception();
        }

        $strAuthName = "App." . static::ucfLabel($objApp->app_name);

        $objAuth = AuthModel::where("auth_name", $strAuthName)->firstOrFail();

        return($objAuth);
    }

    /**
     * @param array $keys
     * @param array $arr
     * @return bool
     */
    public static function array_keys_exists(array $keys, array $arr) : bool
    {
        return(!array_diff_key(array_flip($keys), $arr));
    }

    /**
     * @param array $keys
     * @param array $arr
     * @return array
     */
    public static function array_with_key(array $keys, array $arr) : array
    {
        $arrKeys = array_keys($arr);
        $shareKeys = array_intersect($keys, $arrKeys);
        $result = array();
        foreach($shareKeys as $key)
        {
            $result[$key] = $arr[$key];
        }

        return($result);
    }

    /**
     * @param $bytes
     * @param int $precious
     * @return string
     */
    public static function format_bytes($bytes, int $precious = 2) : string
    {
        $units = array("b", "kb", "mb", "gb", "tb");

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) /log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return(round($bytes, $precious) . " " . static::upperLabel($units[$pow]));
    }

    /**
     * @return string
     */
    public static function memory_usage() : string
    {
        return(static::format_bytes(memory_get_peak_usage()));
    }

    /**
     * @param $fullName
     * @throws Exception
     * @return array
     */
    public static function parse_name(string $fullName) : array
    {
        $arrName = explode(" ", $fullName);
        $name = array();
        switch(count($arrName)) {
            case 1 : {
                $name["name_first"] = $arrName[0];
                break;
            }
            case 2 : {
                $name["name_first"] = $arrName[0];
                $name["name_last"] = $arrName[1];
                break;
            }
            case 3 : {
                $name["name_first"] = $arrName[0];
                $name["name_middle"] = $arrName[1];
                $name["name_last"] = $arrName[2];
                break;
            }
            default:
                throw new Exception("Invalid Parameter.");
        }

        return($name);
    }

    /**
     * @param Collection $collection
     *
     */
    public static function rename_directory(Collection $collection, array $directory) : array
    {
        if (!Util::array_keys_exists(["directory_name", "directory_path", "directory_sortby"], $directory))
            throw new Exception("Invalid Parameter", 400);
        /** @var SupportCollection */
        $directories = $collection->directories;
        if ($directories->isEmpty()) {
            return($directory);
        } else {
            return(static::make_directory_suffix($directory, $collection->directories));
        }
    }

    /**
     * @param array $directory
     * @param SupportCollection $directories
     * @param int $suffix
     * @return array
     */
    public static function make_directory_suffix(array $directory, SupportCollection $directories, ?int $suffix = 0) : array
    {
        if (!Util::array_keys_exists(["directory_name", "directory_path", "directory_sortby"], $directory))
            throw new Exception("Invalid Parameter", 400);
        $result = $directories->search(function ($value) use ($directory, $suffix){
            if ($suffix == 0) {
                return($value->directory_sortby == $directory["directory_sortby"]);
            } else {
                return($value->directory_sortby == sprintf("%s/%s(%s)", $directory["directory_path"], $directory["directory_name"], $suffix));
            }
        });
        if ($result !== false) {
            $suffix++;
            $directory = static::make_directory_suffix($directory, $directories, $suffix);
        } else {
            if ($suffix != 0) {
                $directory["directory_name"] = sprintf("%s(%s)", $directory["directory_name"], $suffix);
                $directory["directory_sortby"] = $directory["directory_path"] . Constant::Separator . $directory["directory_name"];
            }
        }

        return($directory);
    }
    /**
     * @param Collection $collection
     * @param array $file
     * @return array
     */
    public static function rename_file(Collection $collection, array $file) : array
    {
        if (!Util::array_keys_exists(["file_name", "file_path", "file_sortby"], $file))
            throw new Exception("Invalid Parameter", 400);
        /** @var SupportCollection */
        $files = $collection->files;
        if ($files->isEmpty()) {
            return($file);
        } else {
            $file = static::make_suffix($file, $files);
            return($file);
        }
    }

    /**
     * @param array $file
     * @param SupportCollection $files
     * @param int $suffix
     * @return array
     */
    public static function make_suffix(array $file, SupportCollection $files, ?int $suffix = 0) : array
    {
        if (!Util::array_keys_exists(["file_name", "file_path", "file_sortby"], $file))
            throw new Exception("Invalid Parameter", 400);

        $result = $files->search(function($value, $key) use($file, $suffix) {
            if ($suffix == 0) {
                return($value->file_sortby == $file["file_sortby"]);
            } else {
                $fileName = $file["file_name"];
                return($value->file_sortby == sprintf("%s/%s(%s).%s", $file["file_path"], static::file_name($fileName), $suffix, static::file_extension($fileName)));
            }
        });

        if ($result !== false) {
            $suffix++;
            $file = static::make_suffix($file, $files, $suffix);
        } else {
            if ($suffix != 0) {
                $fileName = $file["file_name"];
                $file["file_name"] = sprintf("%s(%s).%s", static::file_name($fileName), $suffix, static::file_extension($fileName));
                $file["file_sortby"] = $file["file_path"] . Constant::Separator . $file["file_name"];
            }
        }

        return($file);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function file_name(string $path) : string
    {
        return(pathinfo($path, PATHINFO_FILENAME));
    }

    /**
     * @param string $path
     * @return string
     */
    public static function file_extension(string $path) : string
    {
        return(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * @param Collection $objLatestCollection
     * @param File $objFile
     *
     * @return int
     */
    public static function getRoot(Collection $objLatestCollection, File $objFile)
    {
        /** @var \Illuminate\Database\Eloquent\Collection */
        $arrFile = $objLatestCollection->files()->where("file_path", $objFile->file_path)->get();
        $arrRoot = [];
        foreach ($arrFile as $itemFile) {
            $historyFile = FileHistory::where("file_id", $itemFile->file_id)->orderBy("collection_id", "desc")->first();
            if ($historyFile) {
                while ($historyFile->parent) {
                    $historyFile = $historyFile->parent()->where("collection_id", "<>", $historyFile->collection_id)->first();
                }
                array_push($arrRoot, ["file" => $itemFile, "root" => $historyFile]);
            }
        }
        $objRootFile = FileHistory::where("file_id", $objFile->file_id)->orderBy("collection_id", "desc")->first();
        while($objRootFile && $objFile->parent) {
            $objRootFile = $objRootFile->parent()
                                        ->where("collection_id", "<>", $objRootFile->collection_id)->first();
        }
        if (count($arrRoot) > 0 && $objRootFile) {
            for ($i = 0; $i < count($arrRoot); $i++) {
                $root = $arrRoot[$i];

                if ($root["root"]->file_id == $objRootFile->file_id && $root["file"]->file_id == $objFile->file_id) {
                    return(0);
                }
                if ($root["root"]->file_id == $objRootFile->file_id && $root["file"]->file_id != $objFile->file_id) {
                    return(1);
                }
            }
        }

        return(2);
    }

    /**
     * @param $objUser
     * @return string
     */
    public static function getAvatarPath($objUser){
        $fileName = $objUser->value("user_uuid") . ".png";
        $path = "users" . Constant::Separator . "avatars" . Constant::Separator . $fileName;

        return ($path);
    }
}
