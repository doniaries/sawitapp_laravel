import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:successmandiri_mobile/core/theme/app_theme.dart';
import 'package:successmandiri_mobile/features/auth/presentation/bloc/auth_cubit.dart';
import 'package:successmandiri_mobile/features/auth/presentation/bloc/auth_state.dart';

class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<AuthCubit, AuthState>(
      builder: (context, state) {
        final user = state is AuthSuccess ? state.user : null;
        
        return Scaffold(
          backgroundColor: AppColors.background,
          body: SingleChildScrollView(
            child: Column(
              children: [
                // Header Profile
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.only(top: 40, bottom: 32, left: 24, right: 24),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.only(
                      bottomLeft: Radius.circular(32),
                      bottomRight: Radius.circular(32),
                    ),
                  ),
                  child: Column(
                    children: [
                      CircleAvatar(
                        radius: 50,
                        backgroundColor: AppColors.primaryContainer.withOpacity(0.1),
                        child: const Icon(Icons.person_rounded, size: 60, color: AppColors.primary),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        user?.name ?? 'Loading...',
                        style: Theme.of(context).textTheme.headlineSmall,
                      ),
                      Text(
                        user?.roles.first.toUpperCase() ?? '-',
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: AppColors.secondary,
                          letterSpacing: 1.2,
                        ),
                      ),
                    ],
                  ),
                ),
                
                Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSectionHeader('Akun'),
                      const SizedBox(height: 16),
                      _buildProfileItem(
                        icon: Icons.badge_outlined,
                        title: 'NIP / ID Karyawan',
                        subtitle: 'SM-${user?.id ?? '000'}',
                      ),
                      _buildProfileItem(
                        icon: Icons.email_outlined,
                        title: 'Email',
                        subtitle: user?.email ?? '-',
                      ),
                      
                      const SizedBox(height: 32),
                      _buildSectionHeader('Perusahaan'),
                      const SizedBox(height: 16),
                      _buildProfileItem(
                        icon: Icons.business_outlined,
                        title: 'Nama Perusahaan',
                        subtitle: user?.perusahaanNama ?? '-',
                      ),
                      
                      const SizedBox(height: 48),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: () => context.read<AuthCubit>().logout(),
                          icon: const Icon(Icons.logout_rounded, size: 20),
                          label: const Text('Keluar dari Aplikasi'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: AppColors.error,
                            side: const BorderSide(color: AppColors.error),
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      Center(
                        child: Text(
                          'Versi 1.0.0 (Stable)',
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            color: AppColors.onSurfaceVariant.withOpacity(0.4),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildSectionHeader(String title) {
    return Text(
      title,
      style: GoogleFonts.manrope(
        fontSize: 16,
        fontWeight: FontWeight.bold,
      ),
    );
  }

  Widget _buildProfileItem({required IconData icon, required String title, required String subtitle}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppColors.onSurfaceVariant.withOpacity(0.6), size: 22),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: GoogleFonts.inter(
                  fontSize: 11,
                  color: AppColors.onSurfaceVariant.withOpacity(0.6),
                ),
              ),
              Text(
                subtitle,
                style: GoogleFonts.manrope(
                  fontWeight: FontWeight.bold,
                  fontSize: 15,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
