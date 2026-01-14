<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo Alert representa notificações ou alertas para o usuário.
 *
 * Contém informações sobre o alerta, estado de leitura e vínculo com o usuário.
 */
class Alert extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'is_read',
    ];

    /**
     * Casts dos atributos para tipos específicos.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Relacionamento: Alerta pertence a um usuário.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar alertas recentes (últimos 7 dias).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }
}
