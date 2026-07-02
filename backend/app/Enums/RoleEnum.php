<?php

namespace App\Enums;

enum RoleEnum: string {
    case GUEST = 'guest';
    case BACKER = 'backer';
    case CREATOR = 'creator';
    case ADMIN = 'admin';
}