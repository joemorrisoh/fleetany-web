<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\ModelRepository;
use App\Entities\Model;
use Illuminate\Support\Facades\Auth;

class ModelRepositoryEloquent extends BaseRepository implements ModelRepository
{

    protected $rules = [
        'model_type_id'   => 'required',
        'name'      => 'min:3|required',
        ];

    public function model()
    {
        return Model::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
    public function results($filters = [])
    {
        $models = $this->scopeQuery(function ($query) use ($filters) {

            $query = $query->select(
                'models.*',
                'types.name as model-type',
                'contacts.name as vendor'
            );
            $query = $query->leftJoin('types', 'models.model_type_id', '=', 'types.id');
            $query = $query->leftJoin('contacts', 'models.vendor_id', '=', 'contacts.id');
            
            if (!empty($filters['model-type'])) {
                $query = $query->where('types.name', 'like', '%'.$filters['model-type'].'%');
            }
            if (!empty($filters['vendor'])) {
                $query = $query->where('contacts.name', 'like', '%'.$filters['vendor'].'%');
            }
            if (!empty($filters['name'])) {
                $query = $query->where('models.name', 'like', '%'.$filters['name'].'%');
            }

            $query = $query->where('models.company_id', Auth::user()['company_id']);
            if ($filters['sort'] == 'model_type') {
                $sort = 'types.name';
            } elseif ($filters['sort'] == 'vendor') {
                $sort = 'contacts.name';
            } else {
                $sort = 'models.'.$filters['sort'];
            }
            $query = $query->orderBy($sort, $filters['order']);
            
            return $query;
        })->paginate($filters['paginate']);
        
        return $models;
    }
    
    public function hasReferences($idModel)
    {
        $model = $this->find($idModel);
        $countReferences = $model->vehicles()->count();
        
        if ($countReferences > 0) {
            return true;
        }
        return false;
    }
    
    public static function getModels($entity_key = null, $idType = null)
    {
        $models = Model::join('types', 'models.model_type_id', '=', 'types.id')
                        ->where('models.company_id', Auth::user()['company_id']);

        if (!empty($entity_key)) {
            $models = $models->where('types.entity_key', $entity_key);
        }

        if (!empty($idType)) {
            $models = $models->where('types.id', $idType);
        }
        
        $models = $models->lists('models.name', 'models.id');
        
        return $models;
    }
}
