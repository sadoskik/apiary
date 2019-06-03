<?php declare(strict_types = 1);

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\NotificationTemplate as NotificationTemplateResource;
use App\Http\Resources\RecruitingCampaignRecipient as RecruitingCampaignRecipientResource;
use Illuminate\Http\Request;

class RecruitingCampaign extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            // Attributes
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Relationships
            'creator' => new UserResource($this->whenLoaded('creator')),
            'template' => new NotificationTemplateResource($this->whenLoaded('template')),
            'recipients' => RecruitingCampaignRecipientResource::collection($this->whenLoaded('recipients')),
        ];
    }
}
