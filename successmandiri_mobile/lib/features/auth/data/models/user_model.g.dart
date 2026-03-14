// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'user_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

UserModel _$UserModelFromJson(Map<String, dynamic> json) => UserModel(
  id: (json['id'] as num).toInt(),
  name: json['name'] as String,
  email: json['email'] as String,
  perusahaanId: (json['perusahaan_id'] as num?)?.toInt(),
  perusahaanNama: json['perusahaan_nama'] as String?,
  isActive: json['is_active'] as bool,
  roles: (json['roles'] as List<dynamic>).map((e) => e as String).toList(),
);

Map<String, dynamic> _$UserModelToJson(UserModel instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'email': instance.email,
  'perusahaan_id': instance.perusahaanId,
  'perusahaan_nama': instance.perusahaanNama,
  'is_active': instance.isActive,
  'roles': instance.roles,
};
