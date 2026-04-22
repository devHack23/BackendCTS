<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Candidate;

#[Fillable(['user_id', 'candidate_id', 'position_id'])]
class Vote extends Model
{
    use HasFactory, Notifiable;
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'candidate_id' => 'integer',
            'position_id' => 'integer',
        ];
    }

    // L'électeur (celui qui vote)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Le candidat pour lequel on vote
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Le poste concerné par ce vote
    public function position()
    {
        return $this->belongsTo(Position::class);
    }


}
