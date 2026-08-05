                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <!-- Roles -->
                    @can('view roles')
                        <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">
                            {{ __('Roles') }}
                        </x-nav-link>
                    @endcan
                    <!-- Permissions -->
                    @can('view permissions')
                        <x-nav-link :href="route('permissions.index')" :active="request()->routeIs('permissions.*')">
                            {{ __('Permissions') }}
                        </x-nav-link>
                    @endcan
                </div>
