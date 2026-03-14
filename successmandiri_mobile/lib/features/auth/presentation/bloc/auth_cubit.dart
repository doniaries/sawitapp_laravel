import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:successmandiri_mobile/core/api/api_service.dart';
import 'package:successmandiri_mobile/features/auth/data/models/user_model.dart';
import 'package:successmandiri_mobile/features/auth/presentation/bloc/auth_state.dart';

class AuthCubit extends Cubit<AuthState> {
  final ApiService _apiService;

  AuthCubit(this._apiService) : super(AuthInitial());

  Future<void> login(String email, String password) async {
    emit(AuthLoading());
    try {
      final response = await _apiService.dio.post('/login', data: {
        'email': email,
        'password': password,
        'device_name': 'mobile_flutter',
      });

      if (response.data['success']) {
        final user = UserModel.fromJson(response.data['data']);
        final token = response.data['token'];

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', token);

        emit(AuthSuccess(user: user, token: token));
      } else {
        emit(AuthFailure(response.data['message'] ?? 'Login gagal.'));
      }
    } catch (e) {
      emit(AuthFailure('Terjadi kesalahan koneksi.'));
    }
  }

  Future<void> checkAuth() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    if (token == null) {
      emit(AuthInitial());
      return;
    }

    try {
      final response = await _apiService.dio.get('/user');
      final user = UserModel.fromJson(response.data);
      emit(AuthSuccess(user: user, token: token));
    } catch (e) {
      emit(AuthInitial());
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.dio.post('/logout');
    } catch (_) {}
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    emit(AuthLogout());
  }
}
