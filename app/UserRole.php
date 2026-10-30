<?php

namespace App;

enum UserRole: string
{
    case SystemAdmin = 'system_admin';
    case AgencyAdmin = 'agency_admin';
    case GroupLeader = 'group_leader';
}
