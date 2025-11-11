<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppLog extends Model
{
    use HasFactory;

    protected $table = 'telescope_entries';
    
    protected $casts = [
        'content' => 'array',
        'created_at' => 'datetime',
    ];

    // CHANGE THIS: Remove type filtering to show ALL logs
    public function scopeLogs($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Keep all other methods exactly the same
    public function getSeverityAttribute()
    {
        $type = $this->attributes['type'] ?? '';
        
        $severityMap = [
            'exception' => 'error',
            'log' => $this->getLogLevelFromContent(),
            'request' => 'info',
            'query' => 'info',
            'command' => 'info',
            'schedule' => 'info',
            'job' => 'info',
            'event' => 'info',
            'mail' => 'info',
            'notification' => 'info',
            'cache' => 'info',
            'redis' => 'info',
        ];

        return $severityMap[$type] ?? 'info';
    }

    public function getLogLevelFromContent()
    {
        $content = $this->content ?? [];
        $level = $content['level'] ?? 'info';
        
        $levelMap = [
            'emergency' => 'error',
            'alert' => 'error',
            'critical' => 'error',
            'error' => 'error',
            'warning' => 'warning',
            'notice' => 'info',
            'info' => 'info',
            'debug' => 'debug',
        ];

        return $levelMap[strtolower($level)] ?? 'info';
    }

    public function getFormattedMessageAttribute()
    {
        $content = $this->content ?? [];
        $type = $this->attributes['type'] ?? '';
        
        switch ($type) {
            case 'exception':
                return $content['message'] ?? 'Exception occurred';
            case 'log':
                return $content['message'] ?? 'Log entry';
            case 'request':
                return $content['method'] . ' ' . ($content['uri'] ?? '') . ' - ' . ($content['response_status'] ?? '');
            case 'query':
                return 'Query: ' . ($content['sql'] ?? '');
            case 'command':
                return 'Command: ' . ($content['command'] ?? '');
            case 'job':
                return 'Job: ' . ($content['name'] ?? '');
            case 'event':
                return 'Event: ' . ($content['name'] ?? '');
            case 'mail':
                return 'Mail: ' . ($content['mailable'] ?? '');
            case 'notification':
                return 'Notification: ' . ($content['name'] ?? '');
            case 'cache':
                return 'Cache: ' . ($content['key'] ?? '') . ' - ' . ($content['type'] ?? '');
            case 'redis':
                return 'Redis: ' . ($content['command'] ?? '');
            default:
                return $content['description'] ?? ucfirst($type);
        }
    }

    public function getBadgeClassAttribute()
    {
        $severity = $this->severity;
        
        $classMap = [
            'error' => 'badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3',
            'warning' => 'badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3',
            'info' => 'badge rounded-pill text-info bg-light-info p-2 text-uppercase px-3',
            'debug' => 'badge rounded-pill text-secondary bg-light-secondary p-2 text-uppercase px-3',
        ];

        return $classMap[$severity] ?? $classMap['info'];
    }

    public function getTypeBadgeClassAttribute()
    {
        $type = $this->attributes['type'] ?? '';
        
        $classMap = [
            'exception' => 'badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3',
            'request' => 'badge rounded-pill text-primary bg-light-primary p-2 text-uppercase px-3',
            'log' => 'badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3',
            'query' => 'badge rounded-pill text-info bg-light-info p-2 text-uppercase px-3',
            'command' => 'badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3',
            'job' => 'badge rounded-pill text-secondary bg-light-secondary p-2 text-uppercase px-3',
            'event' => 'badge rounded-pill text-dark bg-light-dark p-2 text-uppercase px-3',
            'mail' => 'badge rounded-pill text-info bg-light-info p-2 text-uppercase px-3',
            'notification' => 'badge rounded-pill text-primary bg-light-primary p-2 text-uppercase px-3',
            'cache' => 'badge rounded-pill text-warning bg-light-warning p-2 text-uppercase px-3',
            'redis' => 'badge rounded-pill text-danger bg-light-danger p-2 text-uppercase px-3',
        ];

        return $classMap[$type] ?? 'badge rounded-pill text-dark bg-light-dark p-2 text-uppercase px-3';
    }
}