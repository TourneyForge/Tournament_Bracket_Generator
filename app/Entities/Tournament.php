<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Tournament extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];    
    protected $attributes = [
        'id' => null,
        'name' => null,
        'user_id' => null,
        'type' => null,
        'searchable' => null,
        'archive' => null,
        'shuffle_enabled' => null,
        'description' => null,
        'score_bracket' => null,
        'score_enabled' => null, 
        'score_bracket', 
        'increment_score_enabled' => null, 
        'increment_score' => null, 
        'increment_score_type' => null, 
        'visibility' => null, 
        'availability' => null, 
        'available_start' => null, 
        'available_end' => null, 
        'evaluation_method' => null, 
        'voting_accessibility' => null, 
        'voting_mechanism' => null, 
        'round_duration_combine' => null,
        'max_vote_value' => null, 
        'voting_retain' => null, 
        'allow_host_override' => null,
        'pt_image_update_enabled' => null,
        'theme' => null,
        'winner_audio_everyone' => null
    ];
}