<?php

namespace App\Repositories\Soundblock;

use App\Repositories\BaseRepository;
use App\Models\Soundblock\Project;
use Illuminate\Database\Eloquent\Builder;
use Util;

class ProjectRepository extends BaseRepository {
    /**
     * @param Project $objProject
     *
     * @return void
     */
    public function __construct(Project $objProject) {
        $this->model = $objProject;
    }

    /**
     * @param array $where
     * @param string $field = "uuid"
     * @param int $perPage = null
     *
     * @return mixed
     * @throws \Exception
     */
    public function findAllWhere(array $where, string $field = "uuid", ?int $perPage = null) {
        if (($field == "uuid" || $field == "id")) {
            $queryBuilder = $this->model->whereIn("project_" . $field, $where);
        } else {
            throw new \Exception("Invalid Parameter.", 400);
        }

        if ($perPage) {
            $arrProjects = $queryBuilder->paginate($perPage)->withPath(route("get-projects"));
        } else {
            $arrProjects = $queryBuilder->get();
        }

        return ($arrProjects);
    }

    public function findAll(?int $perPage = null, ?string $searchParam = null) {
        $model = $this->model->with("service");

        if (isset($searchParam)) {
            $model = $model->whereRaw("lower(project_title) like (?)", "%" . strtolower($searchParam) . "%");
        }

        if ($perPage) {
            $arrProjects = $model->paginate($perPage);
        } else {
            $arrProjects = $model->get();
        }

        return ($arrProjects);
    }

    public function findAllByDeployment(string $deploymentStatus, int $perPage = null, ?string $searchParam = null) {
        $queryBuilder = $this->model->with(["deployments", "service"])
                                    ->whereHas("deployments", function ($query) use ($deploymentStatus) {
                                        $query->whereRaw("lower(deployment_status) = (?)", strtolower($deploymentStatus));
                                    });

        if (isset($searchParam)) {
            $queryBuilder = $queryBuilder->whereRaw("lower(project_title) like (?)", "%" . strtolower($searchParam) . "%");
        }


        if ($perPage) {
            $arrProjects = $queryBuilder->paginate($perPage);
        } else {
            $arrProjects = $queryBuilder->get();
        }

        return ($arrProjects);
    }

    /**
     * @param array $arrProjectIds
     * @param array $deploymentStatus
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function __getAllByDeploymentStatus(array $arrProjectIds, array $deploymentStatus) {
        return $this->model->with(["deployments.platform"])
                           ->whereHas("deployments", function (Builder $query) use ($deploymentStatus) {
                               $deploymentStatus = array_map([Util::class, "ucfLabel"], $deploymentStatus);
                               $query->whereIn("deployment_status", $deploymentStatus);
                           })->whereIn("project_id", $arrProjectIds)->get();
    }
}
