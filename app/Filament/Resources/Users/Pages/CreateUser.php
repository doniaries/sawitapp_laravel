<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
 
    protected function handleRecordCreation(array $data): Model
    {
        $user = User::where('email', $data['email'])->first();
 
        if ($user) {
            // Jika user sudah ada, update data yang mungkin berubah
            // (kecuali password jika tidak diisi)
            if (isset($data['password']) && filled($data['password'])) {
                $user->password = $data['password'];
            }
            $user->name = $data['name'];
            $user->save();
 
            return $user;
        }
 
        return parent::handleRecordCreation($data);
    }
 
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
