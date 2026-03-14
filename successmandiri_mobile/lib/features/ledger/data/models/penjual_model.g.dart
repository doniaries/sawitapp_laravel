// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'penjual_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

PenjualModel _$PenjualModelFromJson(Map<String, dynamic> json) => PenjualModel(
  id: (json['id'] as num).toInt(),
  nama: json['nama'] as String,
  alamat: json['alamat'] as String?,
  telepon: json['telepon'] as String?,
  hutang: (json['hutang'] as num).toDouble(),
  transaksiCount: (json['transaksi_count'] as num?)?.toInt(),
);

Map<String, dynamic> _$PenjualModelToJson(PenjualModel instance) =>
    <String, dynamic>{
      'id': instance.id,
      'nama': instance.nama,
      'alamat': instance.alamat,
      'telepon': instance.telepon,
      'hutang': instance.hutang,
      'transaksi_count': instance.transaksiCount,
    };
