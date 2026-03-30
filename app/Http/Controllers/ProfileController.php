<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile details.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('profile')
            ->with('status', 'profile-updated');
    }
}
