<section class="py-4 bg-background border-b border-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ mobileMenuOpen: false }">
        <!-- Desktop Menu -->
        <nav class="hidden items-center justify-between lg:flex">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <x-application-logo class="w-8 h-8 fill-current text-foreground" />
                    <span class="text-md font-semibold tracking-tighter text-foreground">
                        {{ config('app.name', 'Laravel') }}
                    </span>
                </a>

                <!-- Navigation Menu -->
                <div class="flex items-center">
                    <div class="flex flex-row gap-1">
                        <!-- Dashboard Link -->
                        <a href="{{ route('dashboard') }}" class="group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors hover:bg-muted hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50 {{ request()->routeIs('dashboard') ? 'bg-accent/50 text-accent-foreground' : 'bg-background' }}">
                            <x-heroicon-o-squares-2x2 class="mr-2 h-4 w-4" />
                            Dashboard
                        </a>

                        <!-- Loans Dropdown -->
                        <x-nav-dropdown active="{{ request()->routeIs(['loans.*', 'kits.*']) }}">
                            <x-slot name="icon">
                                <x-heroicon-o-document-text class="mr-2 h-4 w-4" />
                            </x-slot>
                            <x-slot name="trigger">
                                Loans
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('loans.index')" :active="request()->routeIs('loans.*')">
                                    Loan List
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('kits.index')" :active="request()->routeIs('kits.*')">
                                    Asset Kits
                                </x-dropdown-link>
                            </x-slot>
                        </x-nav-dropdown>

                        <!-- Products Dropdown -->
                        <x-nav-dropdown active="{{ request()->routeIs(['products.*', 'stocks.*', 'assets.*']) }}">
                            <x-slot name="icon">
                                <x-heroicon-o-archive-box class="mr-2 h-4 w-4" />
                            </x-slot>
                            <x-slot name="trigger">
                                Products
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                                    Products
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('assets.index')" :active="request()->routeIs('assets.*')">
                                    Assets
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('stocks.index')" :active="request()->routeIs('stocks.*')">
                                    Stocks
                                </x-dropdown-link>
                            </x-slot>
                        </x-nav-dropdown>

                        <!-- Categories Link -->
                        <a href="{{ route('categories.index') }}" class="group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors hover:bg-muted hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50 {{ request()->routeIs('categories.*') ? 'bg-accent/50 text-accent-foreground' : 'bg-background' }}">
                            <x-heroicon-o-tag class="mr-2 h-4 w-4" />
                            Categories
                        </a>

                        <!-- Locations Link -->
                        <a href="{{ route('locations.index') }}" class="group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors hover:bg-muted hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50 {{ request()->routeIs('locations.*') ? 'bg-accent/50 text-accent-foreground' : 'bg-background' }}">
                            <x-heroicon-o-map-pin class="mr-2 h-4 w-4" />
                            Locations
                        </a>

                        <!-- Users Link -->
                        <a href="{{ route('users.index') }}" class="group inline-flex h-10 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors hover:bg-muted hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50 {{ request()->routeIs('users.*') ? 'bg-accent/50 text-accent-foreground' : 'bg-background' }}">
                            <x-heroicon-o-users class="mr-2 h-4 w-4" />
                            Users
                        </a>

                        <!-- Resources Dropdown -->
                        {{-- <x-nav-dropdown>
                            <x-slot name="icon">
                                <x-heroicon-o-folder class="mr-2 h-4 w-4" />
                            </x-slot>
                            <x-slot name="trigger">
                                Resources
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="#">Help Center</x-dropdown-link>
                                <x-dropdown-link href="#">Contact Us</x-dropdown-link>
                            </x-slot>
                        </x-nav-dropdown> --}}
                    </div>
                </div>
            </div>

            <!-- User Auth Buttons -->
            <div class="flex gap-2">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center justify-center whitespace-nowrap rounded-full text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 gap-2">
                            <span class="hidden md:inline-flex">{{ Auth::user()->name }}</span>
                            <x-avatar :name="Auth::user()->name" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div class="block lg:hidden">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <x-application-logo class="w-8 h-8 fill-current text-foreground" />
                </a>

                <button @click="mobileMenuOpen = true" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10">
                    <x-heroicon-o-bars-3 class="h-4 w-4" />
                </button>
            </div>

            <!-- Mobile Sheet/Drawer -->
            <div x-show="mobileMenuOpen"
                x-transition:enter="duration-300 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="duration-200 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm"
                style="display: none;"
                @click="mobileMenuOpen = false">
            </div>

            <div x-show="mobileMenuOpen"
                x-transition:enter="duration-500 ease-in-out"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="duration-500 ease-in-out"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 z-50 h-full w-3/4 gap-4 border-l bg-background p-6 shadow-lg sm:max-w-sm"
                style="display: none;"
                @click.stop>

                <div class="flex flex-col gap-6">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                            <x-application-logo class="w-8 h-8 fill-current text-foreground" />
                            <span class="text-lg font-semibold">{{ config('app.name') }}</span>
                        </a>
                        <button @click="mobileMenuOpen = false" class="rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <x-heroicon-o-x-mark class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="flex w-full flex-col gap-4">
                        <a href="{{ route('dashboard') }}" class="text-md font-semibold hover:underline {{ request()->routeIs('dashboard') ? 'text-primary' : '' }}">Dashboard</a>

                        <!-- Mobile Loans Accordion -->
                        <div x-data="{ expanded: {{ request()->routeIs(['loans.*', 'kits.*']) ? 'true' : 'false' }} }" class="border-b-0">
                            <button @click="expanded = !expanded" class="flex flex-1 items-center justify-between py-0 font-semibold transition-all hover:underline [&[data-state=open]>svg]:rotate-180 w-full text-left text-md {{ request()->routeIs(['loans.*', 'kits.*']) ? 'text-primary' : '' }}">
                                Loans
                                <x-heroicon-o-chevron-down :class="{'rotate-180': expanded}" class="h-4 w-4 shrink-0 transition-transform duration-200" />
                            </button>
                            <div x-show="expanded" x-collapse>
                                <div class="mt-2 flex flex-col gap-2 pl-4 border-l border-border ml-2">
                                    <a class="text-sm font-medium hover:underline py-1 {{ request()->routeIs('loans.index') ? 'text-primary' : '' }}" href="{{ route('loans.index') }}">Loan List</a>
                                    <a class="text-sm font-medium hover:underline py-1 {{ request()->routeIs('kits.index') ? 'text-primary' : '' }}" href="{{ route('kits.index') }}">Asset Kits</a>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Products Accordion -->
                        <div x-data="{ expanded: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }" class="border-b-0">
                            <button @click="expanded = !expanded" class="flex flex-1 items-center justify-between py-0 font-semibold transition-all hover:underline [&[data-state=open]>svg]:rotate-180 w-full text-left text-md {{ request()->routeIs('products.*') ? 'text-primary' : '' }}">
                                Products
                                <x-heroicon-o-chevron-down :class="{'rotate-180': expanded}" class="h-4 w-4 shrink-0 transition-transform duration-200" />
                            </button>
                            <div x-show="expanded" x-collapse>
                                <div class="mt-2 flex flex-col gap-2 pl-4 border-l border-border ml-2">
                                    <a class="text-sm font-medium hover:underline py-1 {{ request()->routeIs('products.index') ? 'text-primary' : '' }}" href="{{ route('products.index') }}">Products</a>
                                    <a class="text-sm font-medium hover:underline py-1 {{ request()->routeIs('assets.*') ? 'text-primary' : '' }}" href="{{ route('assets.index') }}">Assets</a>
                                    <a class="text-sm font-medium hover:underline py-1 {{ request()->routeIs('stocks.index') ? 'text-primary' : '' }}" href="{{ route('stocks.index') }}">Stocks</a>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('categories.index') }}" class="text-md font-semibold hover:underline {{ request()->routeIs('categories.*') ? 'text-primary' : '' }}">Categories</a>

                        <a href="{{ route('locations.index') }}" class="text-md font-semibold hover:underline {{ request()->routeIs('locations.*') ? 'text-primary' : '' }}">Locations</a>

                        <a href="{{ route('users.index') }}" class="text-md font-semibold hover:underline {{ request()->routeIs('users.*') ? 'text-primary' : '' }}">Users</a>

                        <!-- Mobile User Menu -->
                        <div class="pt-4 mt-4 border-t border-border">
                            <div class="font-medium text-base text-foreground mb-2">{{ Auth::user()->name }}</div>
                            <div class="flex flex-col gap-3">
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2 w-full">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
