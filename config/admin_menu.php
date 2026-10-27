<?php

return [
    ['label' => 'Dashboard',            'icon' => 'bi-grid',              'route' => 'admin.dashboard',          'active' => 'admin.dashboard'],
    ['label' => 'Gestión Turística',    'icon' => 'bi-geo-alt',           'route' => 'admin.destinations.index', 'active' => 'admin.destinations.*'],
    ['label' => 'Moderación de Foro',   'icon' => 'bi-chat',              'route' => 'admin.moderation.index',   'active' => 'admin.moderation.*'],
    ['label' => 'Reportes y Descargas', 'icon' => 'bi-file-earmark-text', 'route' => 'admin.reports.index',      'active' => 'admin.reports.*'],
];