<?php

namespace App\Repositories;

use Util;
use Exception;
use App\Models\BaseModel;
use App\Repositories\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository {
    use Sortable;

    public $sortBy = BaseModel::STAMP_CREATED;
    public $sortOrder = "asc";
    protected Model $model;

    public function all() {
        return ($this->model
            ->orderBy($this->sortBy, $this->sortOrder)
            ->get());
    }

    public function paginated(int $paginate) {
        return ($this->model
            ->orderBy($this->sortBy, $this->sortOrder)
            ->paginate($paginate));
    }

    public function create(array $arrParams) {
        $model = $this->model->newInstance();

        if (!isset($arrParams[$model->uuid()]))
            $arrParams[$model->uuid()] = Util::uuid();

        $model->fill($arrParams);
        $model->save();

        return ($model);
    }

    public function destroy($id) {
        return ($this->find($id)->delete());
    }

    /**
     * @param string|int $id
     * @param bool $bnFailure
     */
    public function find($id, bool $bnFailure = false) {
        if ($bnFailure) {
            if (is_int($id)) {
                return ($this->model->findOrFail($id));
            } else if (is_string($id)) {
                return ($this->model->where($this->model->uuid(), $id)->firstOrFail());
            } else {
                throw new Exception("Invalid Paratmeter.");
            }
        } else {
            if (is_int($id)) {
                return ($this->model->find($id));
            } else if (is_string($id)) {
                return ($this->model->where($this->model->uuid(), $id)->first());
            } else {
                throw new Exception("Invalid Paratmeter.");
            }
        }
    }

    public function update($model, array $arrParams) {
        $model->fill($arrParams);
        $model->save();

        return ($model);
    }
}
