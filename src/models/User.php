<?php namespace Redooor\Redminportal;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements UserInterface, RemindableInterface {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}
    
    public function getRememberToken()
    {
        return $this->remember_token;
    }
    
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }
    
    public function getRememberTokenName()
    {
        return 'remember_token';
    }
    
    public function groups()
    {
        // Based on Cartalyst/Sentry SQL schema
        return $this->belongsToMany('Redooor\Redminportal\Group', 'users_groups');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\Coupon', 'coupon_user');
    }
    
    public function delete()
    {
        $this->coupons()->detach();
        return parent::delete();
    }
    
}