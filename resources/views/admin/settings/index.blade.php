@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h3 class="text-gray-700 text-3xl font-medium">Platform Settings</h3>

    <div class="mt-8">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h4 class="text-lg font-semibold mb-4">Feature Toggles</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Song Battles -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h5 class="font-medium">Song Battles</h5>
                            <p class="text-sm text-gray-500">Enable or disable the Song Battles feature.</p>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="enable_song_battles" value="0">
                            <input type="checkbox" name="enable_song_battles" value="1" 
                                {{ \App\Models\Setting::get('enable_song_battles') == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <!-- AI Music Generation -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h5 class="font-medium">AI Music Generation</h5>
                            <p class="text-sm text-gray-500">Enable or disable AI Music Generation tools.</p>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="enable_ai_music" value="0">
                            <input type="checkbox" name="enable_ai_music" value="1" 
                                {{ \App\Models\Setting::get('enable_ai_music') == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    
                    <!-- Events System -->
                     <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h5 class="font-medium">Events System</h5>
                            <p class="text-sm text-gray-500">Enable or disable Events and Ticketing.</p>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="enable_events" value="0">
                            <input type="checkbox" name="enable_events" value="1" 
                                {{ \App\Models\Setting::get('enable_events') == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <!-- Cloud Storage -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h5 class="font-medium">Cloud Storage</h5>
                            <p class="text-sm text-gray-500">Enable or disable Cloud Storage integration.</p>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="enable_cloud_storage" value="0">
                            <input type="checkbox" name="enable_cloud_storage" value="1" 
                                {{ \App\Models\Setting::get('enable_cloud_storage') == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <!-- Payment System -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h5 class="font-medium">Payment System</h5>
                            <p class="text-sm text-gray-500">Enable or disable Payment processing.</p>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="enable_payments" value="0">
                            <input type="checkbox" name="enable_payments" value="1" 
                                {{ \App\Models\Setting::get('enable_payments') == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <!-- Feed System -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h5 class="font-medium">Feed System</h5>
                            <p class="text-sm text-gray-500">Enable or disable the User Feed.</p>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="enable_feed" value="0">
                            <input type="checkbox" name="enable_feed" value="1" 
                                {{ \App\Models\Setting::get('enable_feed') == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <!-- Zipcode System -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h5 class="font-medium">Zipcode System</h5>
                            <p class="text-sm text-gray-500">Enable or disable Zipcode features.</p>
                        </div>
                        <label class="switch">
                            <input type="hidden" name="enable_zipcodes" value="0">
                            <input type="checkbox" name="enable_zipcodes" value="1" 
                                {{ \App\Models\Setting::get('enable_zipcodes') == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 focus:outline-none">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
</style>
@endsection
