<?php

namespace App\Http\Transformers\Soundblock;


use App\Http\Transformers\ServiceTransformer;
use App\Http\Transformers\Soundblock\DeploymentTransformer;
use App\Models\{BaseModel, Soundblock\File, Soundblock\Project};
use App\Traits\StampCache;
use Storage;
use League\Fractal\TransformerAbstract;
use Util;

class ProjectTransformer extends TransformerAbstract
{
    use StampCache;

    protected $bnLatest;
    protected $bnExtract;
    protected $colIncludes = [];
    public $availableIncludes = [];
    protected $defaultIncludes = [];

    public function __construct($arrIncludes = null, $bnLatest = false, array $colIncludes = null, bool $bnExtract = false)
    {
        $this->bnLatest = $bnLatest;
        $this->colIncludes = $colIncludes;
        $this->bnExtract = $bnExtract;

        if ($arrIncludes)
        {
            foreach($arrIncludes as $item)
            {
                $item = strtolower($item);
                $this->availableIncludes []= $item;
                $this->defaultIncludes []= $item;
            }
        }

    }

    public function transform(Project $objProject)
    {

        $status = $objProject->contracts()->orderBy("contract_id", "desc")->value("flag_status");

        $response = [
            "project_uuid" => $objProject->project_uuid,
            "project_title" => $objProject->project_title,
            "artwork" => $objProject->artwork,
            "project_type" => $objProject->project_type,
            "project_date" => $objProject->project_date,
            "project_upc" => $objProject->project_upc,
            "status" => $status,
        ];

        if ($this->bnExtract) {
            $zipPath = Util::project_zip_path($objProject->project_uuid);
            $arrFiles = Storage::disk("s3-soundblock")->allFiles($zipPath);

            $arrWhere = array();
            foreach ($arrFiles as $file) {
                $fileBaseName = pathinfo($file, PATHINFO_BASENAME);
                array_push($arrWhere, $fileBaseName);
            }

            $arrMusicFile = File::whereIn("file_uuid", $arrWhere)->where("file_category", "music")->get();
            $arrMusicFile->each(function ($item) use ($response) {
                $objMusicFile = $item->music;
                array_push($response["tracks"], [
                    "file_uuid" => $item->file_uuid,
                    "file_name" => $item->file_name,
                    "file_title" => $item->file_tilte,
                    "file_track" => $objMusicFile ? $objMusicFile->file_track : null,
                    "file_duration" => $objMusicFile ? $objMusicFile->file_duration : null,
                ]);
            });
        }

        return(array_merge($response, $this->stamp($objProject)));
    }

    public function includeDeployments(Project $objProject)
    {
        return($this->collection($objProject->deployments, new DeploymentTransformer));
    }

    public function includeService(Project $objProject)
    {
        return($this->item($objProject->service, new ServiceTransformer(["plans"])));
    }
}
