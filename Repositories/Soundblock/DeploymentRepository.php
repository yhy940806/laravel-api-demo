<?php

namespace App\Repositories\Soundblock;

use Util;
use App\Models\{
    BaseModel,
    Soundblock\Deployment,
    Soundblock\Collection,
    Soundblock\Platform,
    Soundblock\Project
};
use App\Repositories\BaseRepository;

class DeploymentRepository extends BaseRepository {
    public function __construct(Deployment $objDeployment) {
        $this->model = $objDeployment;
    }

    public function createModel(array $arrParams) {
        $objDeployment = new Deployment;

        $objDeployment->deployment_uuid = Util::uuid();
        $objDeployment->project_id = $arrParams["project_id"];
        $objDeployment->project_uuid = $arrParams["project_uuid"];
        $objDeployment->platform_id = $arrParams["platform_id"];
        $objDeployment->platform_uuid = $arrParams["platform_uuid"];
        $objDeployment->collection_id = $arrParams["collection_id"];
        $objDeployment->collection_uuid = $arrParams["collection_uuid"];
        $objDeployment->deployment_status = config("constant.soundblock.deployment_status")[0];

        $objDeployment->save();

        return ($objDeployment);
    }

    public function findAllByProject(Project $objProject, array $arrParams = [], ?int $perPage = null) {
        $query = $this->model->join("soundblock_projects", "soundblock_projects_deployments.project_id", "=", "soundblock_projects.project_id")
                             ->join("soundblock_data_platforms", "soundblock_projects_deployments.platform_id", "=", "soundblock_data_platforms.platform_id");

        $query = $query->where("soundblock_projects.project_id", $objProject->project_id);

        if (isset($arrParams["sort_platform"])) {
            $query = $query->orderBy("soundblock_data_platforms.name", Util::lowerLabel($arrParams["sort_platform"]));
        }

        if (isset($arrParams["sort_deployment_status"])) {
            $query = $query->orderBy("soundblock_projects_deployments.deployment_status", Util::lowerLabel($arrParams["sort_deployment_status"]));
        }

        if (isset($arrParams["sort_stamp_updated"])) {
            $query = $query->orderBy(BaseModel::STAMP_UPDATED, Util::lowerLabel($arrParams["sort_stamp_updated"]));
        }

        if ($perPage) {
            $arrDeployments = $query->paginate($perPage);
        } else {
            $arrDeployments = $query->get();
        }

        return ($arrDeployments);
    }

    public function findWhere(array $arrParams) {
        return ($this->model->where(function ($query) use ($arrParams) {
            foreach ($arrParams as $key => $value) {
                $query->where($key, $value);
            }
        })->first());
    }

    public function findLatest(Project $project): ?Deployment {
        return ($this->model->whereHas("project", function ($query) use ($project) {
            $query->where("project_id", $project->project_id);
        })->orderBy("collection_id", "desc")->first());
    }

    /**
     * @param Collection $collection
     * @param Platform $platform
     *
     * @return bool
     */
    public function canDeployOnPlatform(Collection $collection, Platform $platform) {
        return (!$this->model->where("collection_id", $collection->collection_id)
                             ->where("platform_id", $platform->platform_id)->exists());
    }
}
