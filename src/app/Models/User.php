<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo User representa os usuários do sistema.
 *
 * Contém atributos essenciais, proteções e relacionamentos com Projetos, Tarefas e Alertas.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Atributos que devem ser ocultados na serialização.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts dos atributos para tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relacionamento: Um usuário possui muitos projetos.
     *
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Relacionamento: Um usuário possui muitas tarefas.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Relacionamento: Um usuário possui muitos alertas.
     *
     * @return HasMany
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
