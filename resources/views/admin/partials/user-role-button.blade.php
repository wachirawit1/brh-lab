<button type="button"
    class="js-edit-user inline-flex min-h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 text-xs font-bold text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500"
    data-name="{{ $fullName }}"
    data-username="{{ $user->username }}"
    data-position="{{ $user->position ?: 'ไม่ระบุตำแหน่ง' }}"
    data-role-id="{{ $user->role_id }}"
    data-role-name="{{ $user->role_name }}"
    data-update-action="{{ route('admin.users.setRole', $user->username) }}"
    data-delete-action="{{ route('admin.users.destroy', $user->username) }}">
    <i class="fa-solid {{ $user->role_name ? 'fa-pen' : 'fa-user-plus' }}" aria-hidden="true"></i>
    {{ $user->role_name ? ($compact ? 'แก้ไข' : 'แก้ไขสิทธิ์') : 'กำหนดสิทธิ์' }}
</button>
