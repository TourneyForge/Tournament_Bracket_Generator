<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table            = 'participants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'active', 'image', 'hash', 'is_group', 'group_id', 'registered_user_id', 'created_by'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    
    // public function withGroupInfo()
    // {
    //     $this->select('participants.*, groups.id as g_id, groups.group_name, groups.image_path as group_image, groups.user_id as group_creator_id');
    //     $this->join('tournament_members', 'tournament_members.participant_id = participants.id', 'LEFT');
    //     $this->join('grouped_participants', 'grouped_participants.tournament_member_id = tournament_members.id', 'LEFT');
    //     $this->join('groups', 'grouped_participants.group_id = groups.id', 'LEFT');

    //     return $this;
    // }
}