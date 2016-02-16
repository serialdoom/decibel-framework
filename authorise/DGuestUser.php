<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\model\DModel;

/**
 * Singleton guest user model.
 *
 * @author        Timothy de Paris
 */
final class DGuestUser extends DUser
{
    /**
     * Guest User name.
     *
     * @var        string
     */
    const NAME = 'Guest';
    const ID = 2;

    /**
     * Process cache in which the guest user object is stored after first use.
     *
     * @var        DGuestUser
     */
    private static $guestUser;

    /**
     * Loads the Guest User.
     *
     * @note
     * The <code>$id</code> parameter is ignored as {@link DGuestUser}
     * is a singleton model.
     *
     * @param    int $id This parameter is not used.
     *
     * @return    DGuestUser
     */
    public static function create($id = 0)
    {
        // If this is the first time the guest user has been used
        // in this script, load it from the database.
        if (self::$guestUser === null) {
            $guestUser = new DGuestUser(0);
            $guestUser->setFieldValue(self::FIELD_USERNAME, 'guest');
            $guestUser->setFieldValue(self::FIELD_EMAIL, 'guest@example.com');
            $guestUser->setFieldValue(self::FIELD_FIRST_NAME, self::NAME);
            $guestUser->setFieldValue(self::FIELD_LAST_NAME, 'User');
            self::$guestUser = $guestUser;
            // Also add to the local model cache in case it is loaded by ID.
            DModel::$cache[ DGuestUser::ID ] = self::$guestUser;
            DModel::$cacheCount[ DGuestUser::ID ] = 1;
        }

        return self::$guestUser;
    }

    /**
     *
     * @return    int
     */
    public function getId()
    {
        return DGuestUser::ID;
    }

    /**
     * Returns the formatted name of this user.
     *
     * @return    string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns the name for a privilege for this object with the provided suffix.
     *
     * @param    string $suffix   The privilege suffix. A suffix must be comprised
     *                            of an upper case letter followed by one or more
     *                            lower case letters.
     *
     * @return    string
     */
    public function getPrivilegeName($suffix)
    {
        $privilege = parent::getPrivilegeName($suffix);
        if ($privilege) {
            $privilege = str_replace(
                get_class($this),
                DUser::class,
                $privilege
            );
        }

        return $privilege;
    }

    /**
     * Determines the number of minutes this user's login can remain
     * active for.
     *
     * @return    int        The number of minutes this user's login can remain
     *                    active for, or <code>null</code> if no expiry should
     *                    be applied.
     */
    public function getSessionExpiryTime()
    {
        return null;
    }

    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    protected function getStringValue()
    {
        return self::NAME;
    }

    /**
     * Loads the data for the object if it is not a new object. This function must be called
     * by the objects constructor, after setting field information.
     *
     * @param    int  $id         The id of the object to load. Passing an id of
     *                            0 will create a new object.
     * @param    bool $reload     Whether to force a reload of the object.
     *
     * @return    void
     */
    protected function loadFromDatabase($id, $reload = false)
    {
        $this->loadDefaultValues();
    }

    /**
     * Presets the profile for this user if there is only one available profile.
     *
     * Called on the {@link app::decibel::model::DModel::ON_LOAD} event.
     *
     * @return    void
     */
    protected function setInitialProfile()
    {
    }
}
