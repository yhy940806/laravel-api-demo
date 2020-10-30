<?php

namespace App\Listeners\Soundblock;

use Util;
use App\Events\Soundblock\OnHistory;
use App\Services\Soundblock\CollectionHistoryService;
use App\Models\{Common\Debug, BaseModel, User, Soundblock\Collection, Soundblock\CollectionHistory};

class OnHistoryListener {
    protected CollectionHistoryService $historyService;

    /**
     * Create the event listener.
     *
     * @param CollectionHistoryService $historyService
     */
    public function __construct(CollectionHistoryService $historyService) {
        $this->historyService = $historyService;
    }

    /**
     * Handle the event.
     *
     * @param OnHistory $event
     * @return void
     * @throws \Exception
     */
    public function handle($event) {
        $arrHistoryFiles = $event->arrHistoryFiles;
        $objCollection = $event->objCollection;
        $fileAction = $event->fileAction;
        $category = $event->category;
        $objUser = $event->objUser;
        $this->storeDebug($arrHistoryFiles);
        if (is_null($arrHistoryFiles) || $arrHistoryFiles->count() == 0) {
            $historySize = 0;
            $historyCategory = $category;
        } else {
            $historySize = $this->attachFileHistory($objCollection, $arrHistoryFiles, $fileAction, $objUser);
            $historyCategory = $this->getHistoryCategory($arrHistoryFiles);
        }

        $arrHistory = [
            "collection_id"                     => $objCollection->collection_id,
            "collection_uuid"                   => $objCollection->collection_uuid,
            "history_category"                  => $historyCategory,
            "history_size"                      => $historySize,
            "file_action"                       => $fileAction,
            CollectionHistory::STAMP_CREATED_BY => $objUser->user_id,
            CollectionHistory::STAMP_UPDATED_BY => $objUser->user_id,
        ];

        $this->historyService->create($arrHistory);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     *
     * @return void
     */
    private function storeDebug($arrHistoryFile) {
        $debug = new Debug;
        $debug->count = $arrHistoryFile->count();
        /** @var \Illuminate\Support\Collection */
        $arrNewFile = $arrHistoryFile->pluck("new");
        $debug->json = [
            "new" => $arrNewFile->pluck("file_id")->toArray(),
        ];
        $debug->save();
    }

    /**
     * @param Collection $objCollection
     * @param $arrHistoryFiles
     * @param string $fileAction
     * @param User $objUser
     * @return int
     * @throws \Exception
     */
    protected function attachFileHistory(Collection $objCollection, $arrHistoryFiles, string $fileAction, User $objUser): int {
        if (!($arrHistoryFiles instanceof \Illuminate\Database\Eloquent\Collection || $arrHistoryFiles instanceof \Illuminate\Support\Collection)) {
            throw new \Exception();
        }

        $historySize = 0;
        foreach ($arrHistoryFiles as $collect) {
            if (!isset($collect["new"])) {
                throw new \Exception();
            } else if (isset($collect["parent"]) && isset($collect["new"])) {

                $historySize += $collect["new"]->file_size;
                $objCollection->collectionFilesHistory()->attach($collect["new"]->file_id, [
                    "row_uuid"                  => Util::uuid(),
                    "collection_uuid"           => $objCollection->collection_uuid,
                    "parent_id"                 => $collect["parent"]->file_id,
                    "parent_uuid"               => $collect["parent"]->file_uuid,
                    "file_uuid"                 => $collect["new"]->file_uuid,
                    "file_action"               => $fileAction,
                    "file_category"             => $collect["new"]->file_category,
                    "file_memo"                 => "File ( " . $collect["new"]->file_uuid . " )",
                    BaseModel::STAMP_CREATED    => time(),
                    BaseModel::STAMP_CREATED_BY => $objUser->user_id,
                    BaseModel::STAMP_UPDATED    => time(),
                    BaseModel::STAMP_UPDATED_BY => $objUser->user_id,
                ]);

            } else if (isset($collect["new"])) {
                $historySize += $collect["new"]->file_size;
                $objCollection->collectionFilesHistory()->attach($collect["new"]->file_id, [
                    "row_uuid"                  => Util::uuid(),
                    "collection_uuid"           => $objCollection->collection_uuid,
                    "file_uuid"                 => $collect["new"]->file_uuid,
                    "file_action"               => $fileAction,
                    "file_category"             => $collect["new"]->file_category,
                    "file_memo"                 => "File ( " . $collect["new"]->file_uuid . " )",
                    BaseModel::STAMP_CREATED    => time(),
                    BaseModel::STAMP_CREATED_BY => $objUser->user_id,
                    BaseModel::STAMP_UPDATED    => time(),
                    BaseModel::STAMP_UPDATED_BY => $objUser->user_id,
                ]);
            }
        }

        return ($historySize);
    }

    protected function getHistoryCategory($arrHistoryFiles) {
        if (!($arrHistoryFiles instanceof \Illuminate\Support\Collection && $arrHistoryFiles->count() > 0)) {
            throw new \Exception();
        }

        if (!isset($arrHistoryFiles[0]["new"]))
            throw new \Exception();

        $historyCategory = Util::ucfLabel($arrHistoryFiles[0]["new"]->file_category);
        foreach ($arrHistoryFiles as $objFile) {
            foreach ($arrHistoryFiles as $cmpFile) {
                if ($objFile["new"]->file_category != $cmpFile["new"]->file_category) {
                    $historyCategory = "Multiple";
                    return ($historyCategory);
                }
            }
        }
        return ($historyCategory);
    }
}
