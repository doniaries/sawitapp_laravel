// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'operasional_model.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

OperasionalModel _$OperasionalModelFromJson(Map<String, dynamic> json) =>
    OperasionalModel(
      id: (json['id'] as num).toInt(),
      tanggal: DateTime.parse(json['tanggal'] as String),
      operasional: json['operasional'] as String,
      kategori: json['kategori'] as String?,
      kategoriLabel: json['kategori_label'] as String?,
      tipeNama: json['tipe_nama'] as String?,
      namaTerkait: json['nama_terkait'] as String?,
      nominal: (json['nominal'] as num).toDouble(),
      keterangan: json['keterangan'] as String?,
    );

Map<String, dynamic> _$OperasionalModelToJson(OperasionalModel instance) =>
    <String, dynamic>{
      'id': instance.id,
      'tanggal': instance.tanggal.toIso8601String(),
      'operasional': instance.operasional,
      'kategori': instance.kategori,
      'kategori_label': instance.kategoriLabel,
      'tipe_nama': instance.tipeNama,
      'nama_terkait': instance.namaTerkait,
      'nominal': instance.nominal,
      'keterangan': instance.keterangan,
    };
