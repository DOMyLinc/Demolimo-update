<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Version 1 -->
    <div class="bg-gray-700 p-4 rounded">
        <h3 class="text-xl font-bold text-white mb-4">Version 1</h3>
        <div class="mb-4">
            <label class="block text-gray-300 mb-2">Style/Version Name</label>
            <input type="text" name="version_1_style" class="w-full bg-gray-600 text-white rounded px-3 py-2"
                placeholder="e.g. Acoustic" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-300 mb-2">Audio File</label>
            <input type="file" name="version_1_file" class="w-full text-gray-300" accept="audio/*" required>
        </div>
    </div>

    <!-- Version 2 -->
    <div class="bg-gray-700 p-4 rounded">
        <h3 class="text-xl font-bold text-white mb-4">Version 2</h3>
        <div class="mb-4">
            <label class="block text-gray-300 mb-2">Style/Version Name</label>
            <input type="text" name="version_2_style" class="w-full bg-gray-600 text-white rounded px-3 py-2"
                placeholder="e.g. Remix" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-300 mb-2">Audio File</label>
            <input type="file" name="version_2_file" class="w-full text-gray-300" accept="audio/*" required>
        </div>
    </div>

    <!-- Version 3 -->
    <div class="bg-gray-700 p-4 rounded">
        <h3 class="text-xl font-bold text-white mb-4">Version 3</h3>
        <div class="mb-4">
            <label class="block text-gray-300 mb-2">Style/Version Name</label>
            <input type="text" name="version_3_style" class="w-full bg-gray-600 text-white rounded px-3 py-2"
                placeholder="e.g. Live" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-300 mb-2">Audio File</label>
            <input type="file" name="version_3_file" class="w-full text-gray-300" accept="audio/*" required>
        </div>
    </div>
</div>

<div class="text-center">
    <button type="submit"
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full transition duration-300">
        Start Battle
    </button>
</div>
</form>
</div>
@endsection