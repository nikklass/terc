<?php

namespace App\Transformers\ChannelEvent;

use App\Entities\ChannelEvent;
use League\Fractal\TransformerAbstract;

/**
 * Class ChannelEventTransformer.
 */
class ChannelEventTransformer extends TransformerAbstract
{

    /**
     * @var array
     */
    protected $defaultIncludes = ['channel', 'event'];


    /**
     * @param ChannelEvent $model
     * @return array
     */
    public function transform(ChannelEvent $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'channel_id' => $model->channel_id,
            'event_id' => $model->event_id,
            'status' => $model->status()->first(['id', 'name']),           
            'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
            'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
            'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
            'deleted_at' => $deleted_at

        ];

    }

    public function includeProducts(ChannelEvent $model)
    {
        if ($model->products) {
            return $this->collection($model->products, new ProductTransformer());
        } else {
            return null;
        }
    }

    public function includeCharges(ChannelEvent $model)
    {
        if ($model->charges) {
            return $this->collection($model->charges, new ChargeTransformer());
        } else {
            return null;
        }
    }

}
