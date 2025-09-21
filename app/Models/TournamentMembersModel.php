<?php

namespace App\Models;

use CodeIgniter\Model;

class TournamentMembersModel extends Model
{
    protected $table            = 'tournament_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tournament_id', 'participant_id', 'order', 'created_by', 'hash'];

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

    public function participantInfo()
    {
        $this->select('participants.*, tournament_members.order, tournament_members.created_by as hosted_by, groups.id as g_id, groups.group_name, groups.image_path as group_image');
        $this->join('participants', 'tournament_members.participant_id = participants.id', 'LEFT');
        $this->join('group_members', 'tournament_members.id = group_members.tournament_member_id', 'LEFT');
        $this->join('groups', 'group_members.group_id = groups.id', 'LEFT');

        return $this;
    }
}