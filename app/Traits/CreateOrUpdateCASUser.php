<?php

declare(strict_types=1);

namespace App\Traits;

use App\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

trait CreateOrUpdateCASUser
{
    /**
     * CAS library interface.
     *
     * @var \Subfission\Cas\CasManager
     */
    protected $cas;

    public function __construct()
    {
        $this->cas = app('cas');
    }

    public function createOrUpdateCASUser(): User
    {
        $attrs = ['gtGTID', 'email_primary', 'givenName', 'sn'];
        // Attributes that will be split by commas when masquerading
        $arrayAttrs = ['gtPersonEntitlement'];
        // Merge them together so we verify all attributes are present, even the array ones
        $attrs = array_merge($attrs, $arrayAttrs);
        if ($this->cas->isMasquerading()) {
            $masq_attrs = [];
            foreach ($attrs as $attr) {
                $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
            }
            // Split the attributes that we need to split
            foreach ($arrayAttrs as $attr) {
                $masq_attrs[$attr] = explode(',', $masq_attrs[$attr]);
            }
            $this->cas->setAttributes($masq_attrs);
        }

        foreach ($attrs as $attr) {
            if (! $this->cas->hasAttribute($attr) || null === $this->cas->getAttribute($attr)) {
                throw new Exception('Missing attributes from CAS');
            }
        }

        //User is starting a new session, so let's update data from CAS
        //Sadly we can't use updateOrCreate here because of $guarded in the User model
        $user = User::where('uid', $this->cas->user())->first();
        if (null === $user) {
            $user = new User();
        }
        $user->uid = $this->cas->user();
        $user->gtid = $this->cas->getAttribute('gtGTID');
        $user->gt_email = $this->cas->getAttribute('email_primary');
        $user->first_name = $this->cas->getAttribute('givenName');
        $user->last_name = $this->cas->getAttribute('sn');
        $user->save();

        //Initial Role Assignment
        if ($user->wasRecentlyCreated || 0 === $user->roles->count()) {
            $role = Role::where('name', 'non-member')->first();
            if (null !== $role) {
                $user->assignRole($role);
            } else {
                Log::error(self::class."Role 'non-member' not found for assignment to ".$user->uid);
            }
        }

        //Role update based on active status (in case it didn't happen elsewhere)
        if (true === $user->is_active && $user->hasRole('non-member')) {
            Log::info(self::class.': Updating role membership for '.$user->uid);
            $user->removeRole('non-member');
            $role_member = Role::where('name', 'member')->first();
            if (null !== $role_member && ! $user->hasRole('member')) {
                $user->assignRole($role_member);
            } else {
                Log::error(self::class.": Role 'member' not found for assignment to ".$user->uid);
            }
        }

        return $user;
    }
}
