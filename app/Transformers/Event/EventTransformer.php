<?php

namespace App\Transformers\Event;

use App\Entities\Event;
use League\Fractal\TransformerAbstract;

/**
 * Class EventTransformer.
 */
class EventTransformer extends TransformerAbstract
{

    /**
     * @param Event $model
     * @return array
     */
    public function transform(Event $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'event_cd' => $model->event_cd,
            'description' => $model->description,
            'status' => $model->status()->first(['id', 'name']),           
            'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
            'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
            'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
            'deleted_at' => $deleted_at

        ];

    }

}
