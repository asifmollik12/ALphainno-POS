<form method="POST" action="{{ $action }}" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 max-w-xl shadow-sm">
    @csrf
    @if ($model ?? null) @method('PUT') @endif
    <div><label class="block text-sm font-medium mb-1">Name</label><input name="name" value="{{ old('name', $model->name ?? '') }}" required class="w-full rounded-lg border-slate-300"></div>
    <div><label class="block text-sm font-medium mb-1">Email</label><input type="email" name="email" value="{{ old('email', $model->email ?? '') }}" class="w-full rounded-lg border-slate-300"></div>
    <div><label class="block text-sm font-medium mb-1">Phone</label><input name="phone" value="{{ old('phone', $model->phone ?? '') }}" class="w-full rounded-lg border-slate-300"></div>
    <div><label class="block text-sm font-medium mb-1">Address</label><textarea name="address" rows="3" class="w-full rounded-lg border-slate-300">{{ old('address', $model->address ?? '') }}</textarea></div>
    <button class="px-5 py-2 bg-violet-600 text-white rounded-lg">{{ ($model ?? null) ? 'Save' : 'Create' }}</button>
</form>
