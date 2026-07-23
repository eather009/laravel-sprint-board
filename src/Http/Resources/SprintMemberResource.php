<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Resources;

use Eather009\LaravelSprintBoard\Enums\SprintMemberRole;
use Eather009\LaravelSprintBoard\Models\SprintMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SprintMember */
class SprintMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = $this->role;

        return [
            'id' => $this->id,
            'sprint_id' => $this->sprint_id,
            'user_id' => $this->user_id,
            'display_name' => $this->display_name,
            'role' => $role instanceof SprintMemberRole ? $role->value : $role,
        ];
    }
}
