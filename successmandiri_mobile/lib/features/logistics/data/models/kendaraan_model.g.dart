// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'kendaraan_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

KendaraanModel _$KendaraanModelFromJson(Map<String, dynamic> json) =>
    KendaraanModel(
      id: (json['id'] as num).toInt(),
      noPolisi: json['no_polisi'] as String,
      supirId: (json['supir_id'] as num).toInt(),
      supirNama: json['supir_nama'] as String?,
    );

Map<String, dynamic> _$KendaraanModelToJson(KendaraanModel instance) =>
    <String, dynamic>{
      'id': instance.id,
      'no_polisi': instance.noPolisi,
      'supir_id': instance.supirId,
      'supir_nama': instance.supirNama,
    };
