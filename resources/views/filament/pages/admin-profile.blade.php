<div style="display: grid; gap: 1.25rem; max-width: 56rem;">
    <div style="border: 1px solid rgba(255,255,255,0.08); border-radius: 1.25rem; background: linear-gradient(135deg, rgba(251,191,36,0.16), rgba(24,24,27,0.98) 42%); box-shadow: 0 12px 32px rgba(0,0,0,0.28); overflow: hidden;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.5rem;">
            <div style="min-width: 0;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 999px; background: rgba(251,191,36,0.14); padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #fcd34d;">
                    <span style="height: 0.5rem; width: 0.5rem; border-radius: 999px; background: #f59e0b;"></span>
                    Admin profile
                </div>

                <h1 style="margin-top: 0.9rem; font-size: 1.75rem; font-weight: 800; line-height: 1.1; color: #fff;">
                    My Profile
                </h1>

                <p style="margin-top: 0.5rem; max-width: 42rem; font-size: 0.95rem; line-height: 1.6; color: rgba(255,255,255,0.72);">
                    Update your account details, password, and access settings from one dedicated place.
                </p>
            </div>

            <a
                href="{{ \Filament\Facades\Filament::getUrl() }}"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.8rem; background: #f59e0b; padding: 0.85rem 1.1rem; font-size: 0.95rem; font-weight: 800; color: #111827; text-decoration: none; box-shadow: 0 10px 18px rgba(245,158,11,0.18); transition: transform 150ms ease, box-shadow 150ms ease;"
                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 14px 24px rgba(245,158,11,0.24)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 18px rgba(245,158,11,0.18)'"
            >
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" style="height: 1rem; width: 1rem;">
                    <path fill-rule="evenodd" d="M9.293 3.293a1 1 0 0 1 1.414 0l6 6a1 1 0 0 1-1.414 1.414L11 6.414V16a1 1 0 1 1-2 0V6.414L4.707 10.707A1 1 0 0 1 3.293 9.293l6-6Z" clip-rule="evenodd" />
                </svg>
                Go to Dashboard
            </a>
        </div>
    </div>

    <div style="border: 1px solid rgba(255,255,255,0.08); border-radius: 1rem; background: rgba(24,24,27,0.98); box-shadow: 0 8px 24px rgba(0,0,0,0.25);">
        <div style="padding: 1.5rem;">
            <livewire:profile.update-profile-information-form />
        </div>
    </div>

    <div style="border: 1px solid rgba(255,255,255,0.08); border-radius: 1rem; background: rgba(24,24,27,0.98); box-shadow: 0 8px 24px rgba(0,0,0,0.25);">
        <div style="padding: 1.5rem;">
            <livewire:profile.update-password-form />
        </div>
    </div>

    <div style="border: 1px solid rgba(248,113,113,0.24); border-radius: 1rem; background: rgba(24,24,27,0.98); box-shadow: 0 8px 24px rgba(0,0,0,0.25);">
        <div style="padding: 1.5rem;">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</div>
