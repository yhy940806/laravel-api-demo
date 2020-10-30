<?php

namespace App\Http\Transformers\Soundblock;

use App\Models\Soundblock\Collection;
use App\Models\Soundblock\File;
use App\Models\Soundblock\FileHistory;
use App\Traits\StampCache;
use League\Fractal\TransformerAbstract;

class FileTransformer extends TransformerAbstract
{
    use StampCache;

    protected $bnHistory;
    protected $objLatestCol;
    protected $objPresentCol;
    public $availableIncludes = [

    ];

    protected $defaultIncludes = [

    ];

    public function __construct($arrIncludes = null, Collection $objLatestCol = null, $bnHistory = false)
    {
        $this->bnHistory = $bnHistory;

        $this->objLatestCol = $objLatestCol;

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

    public function transform(File $objFile)
    {

        $response = [
            "file_uuid"             => $objFile->file_uuid,
            "file_name"             => $objFile->file_name,
            "file_path"             => $objFile->file_path,
            "file_title"            => $objFile->file_title,
            "file_category"         => $objFile->file_category,
            "file_sortby"           => $objFile->file_sortby,
            "file_size"             => $objFile->file_size,
            "meta"                  => $objFile->meta,
            File::STAMP_CREATED     => $objFile->{File::STAMP_CREATED},
            File::STAMP_CREATED_BY  => $objFile->{File::STAMP_CREATED_BY},
            File::STAMP_UPDATED     => $objFile->{File::STAMP_UPDATED},
            File::STAMP_UPDATED_BY  => $objFile->{File::STAMP_UPDATED_BY},
        ];

        if ($objFile->pivot)
        {
            if ($objFile->pivot->file_action && $objFile->pivot->file_category)
            {
                $response["file_action"] = $objFile->pivot->file_action;
                $response["file_category"] = $objFile->pivot->file_category;
                $response["file_memo"] = $objFile->pivot->file_memo;
            }
        }

        $historyFile = FileHistory::where("file_id", $objFile->file_id)
                                        ->orderBy("collection_id","desc")
                                        ->first();
        $arrHistory = array();
        if ($historyFile && false)
        {
            $historyItem = [
                "file_uuid" => $historyFile->file_uuid,
                "file_action" => $historyFile->file_action,
            ];
            array_push($arrHistory, array_merge($historyItem, $this->stamp($historyFile)));

            while($historyFile->parent)
            {
                $historyFile = $historyFile->parent()
                                            ->where("collection_id", "<>", $historyFile->collection_id)->first();
                $action = $historyFile->file_action;
                $historyItem = [
                    "file_uuid" => $historyFile->file_uuid,
                    "file_action" => $action,
                ];
                array_push($arrHistory,array_merge($historyItem, $this->stamp($historyFile)));
            }

            $response["file_history"] = $arrHistory;
        }

        return($response);
    }

    public function includeMusic(File $objFile)
    {
        return($this->item($objFile->music, new FileMusicTransformer));
    }

    public function includeVideo(File $objFile)
    {
        return($this->item($objFile->video, new FileVideoTransformer(["track"])));
    }

    public function includeMerch(File $objFile)
    {
        return($this->item($objFile->merch, new FileMerchTransformer));
    }

    public function includeOther(File $objFile)
    {
        return($this->item($objFile->other, new FileOtherTransformer));
    }

    protected function getRoot(Collection $objLatestCol, File $objFile)
    {
        $arrObjFiles = $objLatestCol->files()->where("file_path", $objFile->file_path)->get();

        $arrRoot = array();
        foreach($arrObjFiles as $itemFile)
        {
            $historyFile = FileHistory::where("file_id", $itemFile->file_id)->orderBy("collection_id", "desc")->first();
            if ($historyFile) {
                while($historyFile->parent)
                {
                    $historyFile = $historyFile->parent()->where("collection_id", "<>", $historyFile->collection_id)->first();
                }
                array_push($arrRoot, ["file" => $itemFile, "root" => $historyFile]);
            }
        }

        $objRootFile = FileHistory::where("file_id", $objFile->file_id)->orderBy("collection_id", "desc")->first();

        while($objRootFile && $objRootFile->parent)
        {
            $objRootFile = $objRootFile->parent()
                                    ->where("collection_id", "<>", $objRootFile->collection_id)->first();
        }
        if (count($arrRoot) >0 && $objRootFile)
        {
            for ($i = 0; $i < count($arrRoot); $i++)
            {
                $root = $arrRoot[$i];

                if ($root["root"]->file_id == $objRootFile->file_id && $root["file"]->file_id == $objFile->file_id)
                {
                    return 0;
                }
                if ($root["root"]->file_id == $objRootFile->file_id && $root["file"]->file_id != $objFile->file_id)
                {
                    return 1;
                }
            }
        }

        return 2;
    }
}
