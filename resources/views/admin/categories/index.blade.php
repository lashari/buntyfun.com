<x-admin-layout title="Categories">
    <h1 class="text-2xl font-bold">Categories</h1>
    <p class="mt-1 text-sm text-slate-500">Ad placement is controlled per department here. Condolence (when it ships) hardcodes ads off regardless of this setting.</p>

    <table class="mt-6 w-full overflow-hidden rounded-xl border border-slate-200 bg-white text-sm">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Department</th>
                <th class="px-4 py-2">Ads</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($categories as $category)
                <tr>
                    <td class="px-4 py-2">{{ $category->name }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ $category->department }}</td>
                    <td class="px-4 py-2">
                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name" value="{{ $category->name }}">
                            <input type="checkbox" name="ads_enabled" value="1" onchange="this.form.submit()" @checked($category->ads_enabled)>
                            <span class="text-slate-500">{{ $category->ads_enabled ? 'Enabled' : 'Disabled' }}</span>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-admin-layout>
