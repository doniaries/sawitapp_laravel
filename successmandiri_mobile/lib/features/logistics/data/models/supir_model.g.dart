// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'supir_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

SupirModel _$SupirModelFromJson(Map<String, dynamic> json) => SupirModel(
  id: (json['id'] as num).toInt(),
  nama: json['nama'] as String,
  alamat: json['alamat'] as String?,
  telepon: json['telepon'] as String?,
  hutang: (json['hutang'] as num).toDouble(),
  kendaraanCount: (json['kendaraan_count'] as num?)?.toInt(),
);

Map<String, dynamic> _$SupirModelToJson(SupirModel instance) =>
    <String, dynamic>{
      'id': instance.id,
      'nama': instance.nama,
      'alamat': instance.alamat,
      'telepon': instance.telepon,
      'hutang': instance.hutang,
      'kendaraan_count': instance.kendaraanCount,
    };
