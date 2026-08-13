<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Featured Facebook Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Featured Facebook post URL') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('This post is shown in the "Latest from Ezefone" panel on the homepage and on the Media Posts page. Paste the permalink of the Facebook post you want featured — leave it blank to hide both.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('admin.featured-post.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="url" :value="__('Featured Facebook post URL')" />
                                <x-text-input
                                    id="url"
                                    name="url"
                                    type="url"
                                    class="mt-1 block w-full"
                                    :value="old('url', $url)"
                                    placeholder="https://www.facebook.com/permalink.php?story_fbid=...&id=..."
                                    autocomplete="off"
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('url')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('status') === 'featured-post-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600"
                                    >{{ __('Saved.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
