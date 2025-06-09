<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Scheme;
use App\Models\DataIot;
use App\Models\Sensor;
use App\Models\SchemeSensor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'deleted' => false
        ]);
        
        // Create regular users
        $users = [];
        for ($i = 1; $i <= 25; $i++) {
            $users[] = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'is_admin' => ($i % 5 == 0), // Every 5th user is admin
                'deleted' => false
            ]);
        }

        // Create sensors - more variety
        $sensorTypes = [
            ['name' => 'Temperature', 'output_labels' => 'temperature', 'num_of_outputs' => 1],
            ['name' => 'Humidity', 'output_labels' => 'humidity', 'num_of_outputs' => 1],
            ['name' => 'Pressure', 'output_labels' => 'pressure', 'num_of_outputs' => 1],
            ['name' => 'Light', 'output_labels' => 'light_level', 'num_of_outputs' => 1],
            ['name' => 'Soil Moisture', 'output_labels' => 'moisture', 'num_of_outputs' => 1],
            ['name' => 'Air Quality', 'output_labels' => 'co2,pm25,voc', 'num_of_outputs' => 3],
            ['name' => 'Weather Station', 'output_labels' => 'temperature,humidity,pressure', 'num_of_outputs' => 3],
            ['name' => 'GPS', 'output_labels' => 'latitude,longitude,altitude', 'num_of_outputs' => 3],
            ['name' => 'Motion Sensor', 'output_labels' => 'motion_detected,distance', 'num_of_outputs' => 2],
            ['name' => 'Water Quality', 'output_labels' => 'ph,turbidity,temperature', 'num_of_outputs' => 3],
            ['name' => 'Power Monitor', 'output_labels' => 'voltage,current,power', 'num_of_outputs' => 3],
            ['name' => 'Heart Rate', 'output_labels' => 'bpm,oxygen_saturation', 'num_of_outputs' => 2],
            ['name' => 'UV Index', 'output_labels' => 'uv_index', 'num_of_outputs' => 1],
            ['name' => 'Smoke Detector', 'output_labels' => 'smoke_level,co_level', 'num_of_outputs' => 2],
            ['name' => 'Gas Sensor', 'output_labels' => 'methane,propane,hydrogen', 'num_of_outputs' => 3],
            ['name' => 'Distance Sensor', 'output_labels' => 'distance', 'num_of_outputs' => 1],
            ['name' => 'Flame Detector', 'output_labels' => 'flame_detected', 'num_of_outputs' => 1],
            ['name' => 'Proximity Sensor', 'output_labels' => 'object_present', 'num_of_outputs' => 1],
            ['name' => 'Sound Sensor', 'output_labels' => 'decibels', 'num_of_outputs' => 1],
            ['name' => 'Gyroscope', 'output_labels' => 'x_axis,y_axis,z_axis', 'num_of_outputs' => 3]
        ];

        $sensors = [];
        foreach ($sensorTypes as $sensorType) {
            $sensors[] = Sensor::create([
                'name' => $sensorType['name'],
                'output_labels' => $sensorType['output_labels'],
                'num_of_outputs' => $sensorType['num_of_outputs'],
                'deleted' => false
            ]);
        }

        // Create schemes with sensors
        foreach ($users as $user) {
            for ($i = 1; $i <= 2; $i++) {
                // For each scheme, choose 2 random sensors
                $selectedSensors = collect($sensors)->random(2)->values();
                
                // Build columns string from selected sensors
                $columns = [];
                foreach ($selectedSensors as $sensor) {
                    $columns = array_merge($columns, explode(',', $sensor->output_labels));
                }
                $columnsStr = implode(',', $columns);
                $numOfCol = count($columns);
                
                $scheme = Scheme::create([
                    'id' => Str::uuid(),
                    'columns' => $columnsStr,
                    'num_of_col' => $numOfCol,
                    'user_id' => $user->id,
                    'deleted' => false
                ]);

                // Link sensors to scheme
                foreach ($selectedSensors as $index => $sensor) {
                    SchemeSensor::create([
                        'scheme_id' => $scheme->id,
                        'sensor_id' => $sensor->id,
                        'order' => $index,
                        'deleted' => false
                    ]);
                }

                // Create sample data for each month
                $months = [
                    '2024-01-01', '2024-02-01', '2024-03-01',
                    '2024-04-01', '2024-05-01', '2024-06-01'
                ];

                foreach ($months as $month) {
                    $count = rand(10, 30);
                    for ($j = 0; $j < $count; $j++) {
                        // Generate random data for each column
                        $contentValues = [];
                        for ($col = 0; $col < $numOfCol; $col++) {
                            $contentValues[] = rand(10, 100);
                        }
                        
                        DataIot::create([
                            'scheme_id' => $scheme->id,
                            'user_id' => $user->id,
                            'content' => implode(',', $contentValues),
                            'created_at' => date('Y-m-d H:i:s', strtotime($month . ' + ' . rand(1, 28) . ' days')),
                            'deleted' => false
                        ]);
                    }
                }
            }
        }
    }
}
