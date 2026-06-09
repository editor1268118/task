<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     * Create a new UserService instance.
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get paginated list of users.
     */
    public function getAllUsers(int $perPage = 15)
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data)
    {
        $data['employee_id'] = $this->userRepository->generateEmployeeId();
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }

    /**
     * Update an existing user.
     */
    public function updateUser(int $id, array $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $this->userRepository->update($id, $data);

        $user = $this->userRepository->find($id);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user;
    }

    /**
     * Delete a user (soft delete).
     */
    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     * Find a user by ID.
     */
    public function findUser(int $id)
    {
        return $this->userRepository->findOrFail($id);
    }

    /**
     * Suspend a user.
     */
    public function suspendUser(int $id): bool
    {
        return $this->userRepository->update($id, ['status' => 'suspended']);
    }

    /**
     * Activate a user.
     */
    public function activateUser(int $id): bool
    {
        return $this->userRepository->update($id, ['status' => 'active']);
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName)
    {
        return $this->userRepository->getUsersByRole($roleName);
    }

    /**
     * Get active users.
     */
    public function getActiveUsers()
    {
        return $this->userRepository->getActiveUsers();
    }
}
