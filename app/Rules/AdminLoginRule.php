<?php

namespace App\Rules;

use App\Repositories\AdminsRepository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class AdminLoginRule implements Rule
{
    protected $mobile;
    protected $adminsRepository;

    /**
 * AdminLoginRule constructor.
 * @param AdminsRepository $adminsRepository
 */
    public function __construct(AdminsRepository $adminsRepository,$mobile)
    {
        $this->mobile = $mobile;
        $this->adminsRepository = $adminsRepository;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $admin = $this->adminsRepository->ByMobile($this->mobile);

        if(is_null($admin)) return false;

        //验证密码是否正确
        return Hash::check($value,$admin->password) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '密码错误';
    }
}
