import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:successmandiri_mobile/core/api/api_service.dart';
import 'package:successmandiri_mobile/core/theme/app_theme.dart';
import 'package:successmandiri_mobile/features/auth/presentation/bloc/auth_cubit.dart';
import 'package:successmandiri_mobile/features/auth/presentation/bloc/auth_state.dart';
import 'package:successmandiri_mobile/features/auth/presentation/pages/login_page.dart';
import 'package:successmandiri_mobile/features/dashboard/presentation/pages/dashboard_page.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    final apiService = ApiService();

    return MultiBlocProvider(
      providers: [
        BlocProvider(create: (_) => AuthCubit(apiService)..checkAuth()),
      ],
      child: MaterialApp(
        title: 'Success Mandiri',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        home: BlocBuilder<AuthCubit, AuthState>(
          builder: (context, state) {
            if (state is AuthSuccess) {
              return const DashboardPage();
            }
            return const LoginPage();
          },
        ),
      ),
    );
  }
}
