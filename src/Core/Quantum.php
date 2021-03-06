<?php
namespace Zjien\Quantum;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Quantum
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Create a new Instance
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Determine if the given uri and method should be granted for the current user.
     *
     * @param string $uri
     * @param string $method
     * @return bool
     * @throws bool|UnauthorizedHttpException|NotFoundHttpException
     */
    public function check($uri, $method)
    {
        $user = $this->user();
        if (!$user) {
            throw new UnauthorizedHttpException('');
        }

        $roles = $user->roles;

        foreach ($roles as $role) {
            $permissions = $role->permissions;
            foreach ($permissions as $perm) {
                if ($perm->uri == $uri && $perm->verb == $method) {
                    if ($perm::STATUS_CLOSING == $perm->status) {
                        throw new NotFoundHttpException();
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get the current user.
     *
     * @return Illuminate\Auth\UserInterface|null
     */
    public function user()
    {
        return $this->app->auth->user();
    }

    /**
     * Normalize the params.
     *
     * @param array|Model|Collection $value
     * @return array
     */
    public static function normalize($value)
    {
        $result = [];

        if ($value instanceof Collection) {
            foreach ($value as $val) {
                $result[] = $val->getKey();
            }
        } else if ($value instanceof Model) {
            $result = [$value->getKey()];
        } else if (!is_array($value)) {
            $result = [$value];
        } else {
            $result = $value;
        }

        return $result;
    }

}