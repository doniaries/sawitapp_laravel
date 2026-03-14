import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  // Brand Colors
  static const Color primary = Color(0xFF0D631B);
  static const Color primaryContainer = Color(0xFF2E7D32);
  static const Color onPrimary = Colors.white;
  
  static const Color secondary = Color(0xFF9C4400); // Burnt Orange
  static const Color secondaryContainer = Color(0xFFFD7613);
  
  static const Color tertiary = Color(0xFF923357);
  
  // Neutral Colors
  static const Color background = Color(0xFFFBF9F8);
  static const Color surface = Color(0xFFFBF9F8);
  static const Color surfaceLow = Color(0xFFF5F3F3);
  static const Color onSurface = Color(0xFF1B1C1C);
  static const Color onSurfaceVariant = Color(0xFF40493D);
  static const Color outline = Color(0xFF707A6C);
  static const Color outlineVariant = Color(0xFFC4C8BA);
  static const Color surfaceVariant = Color(0xFFDFE4D7);
  
  // Semantic
  static const Color error = Color(0xFFBA1A1A);
}

class AppTheme {
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.primary,
        primary: AppColors.primary,
        onPrimary: AppColors.onPrimary,
        secondary: AppColors.secondary,
        background: AppColors.background,
        surface: AppColors.surface,
        error: AppColors.error,
      ),
      textTheme: GoogleFonts.interTextTheme().copyWith(
        displayLarge: GoogleFonts.manrope(
          fontWeight: FontWeight.w800,
          color: AppColors.onSurface,
        ),
        displayMedium: GoogleFonts.manrope(
          fontWeight: FontWeight.w700,
          color: AppColors.onSurface,
        ),
        headlineLarge: GoogleFonts.manrope(
          fontWeight: FontWeight.w800,
          color: AppColors.onSurface,
        ),
        headlineMedium: GoogleFonts.manrope(
          fontWeight: FontWeight.w700,
          color: AppColors.onSurface,
        ),
        titleLarge: GoogleFonts.manrope(
          fontWeight: FontWeight.w600,
          color: AppColors.onSurface,
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          textStyle: GoogleFonts.manrope(fontWeight: FontWeight.bold),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          padding: const EdgeInsets.symmetric(vertical: 16),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surfaceLow,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.primary, width: 2),
        ),
        hintStyle: GoogleFonts.inter(color: AppColors.outline),
      ),
    );
  }
}
