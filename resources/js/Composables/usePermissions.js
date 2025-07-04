import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
  const page = usePage();

  const userPermissions = page.props.userPermissions || [];
  const deniedPermissions = page.props.deniedPermissions || [];

  function can(permission) {
    if (userPermissions.includes('*') && !deniedPermissions.includes(permission)) {
      return true; // owner con todo, salvo que se le haya denegado explícitamente
    }
    if (deniedPermissions.includes(permission)) {
      return false;
    }
    return userPermissions.includes(permission);
  }

  return { can };
}
