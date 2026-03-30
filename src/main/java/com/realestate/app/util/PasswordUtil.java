package com.realestate.app.util;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.util.Base64;

public final class PasswordUtil {
    private static final SecureRandom RANDOM = new SecureRandom();

    private PasswordUtil() {
    }

    public static String hashPassword(String plainTextPassword) {
        byte[] salt = new byte[16];
        RANDOM.nextBytes(salt);
        byte[] hash = sha256(salt, plainTextPassword);
        return Base64.getEncoder().encodeToString(salt) + ":" + Base64.getEncoder().encodeToString(hash);
    }

    public static boolean verifyPassword(String plainTextPassword, String storedValue) {
        if (storedValue == null || !storedValue.contains(":")) {
            return false;
        }

        String[] parts = storedValue.split(":");
        byte[] salt = Base64.getDecoder().decode(parts[0]);
        byte[] expectedHash = Base64.getDecoder().decode(parts[1]);
        byte[] actualHash = sha256(salt, plainTextPassword);

        if (expectedHash.length != actualHash.length) {
            return false;
        }

        for (int index = 0; index < expectedHash.length; index++) {
            if (expectedHash[index] != actualHash[index]) {
                return false;
            }
        }

        return true;
    }

    private static byte[] sha256(byte[] salt, String plainTextPassword) {
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            digest.update(salt);
            digest.update(plainTextPassword.getBytes(StandardCharsets.UTF_8));
            return digest.digest();
        } catch (NoSuchAlgorithmException exception) {
            throw new IllegalStateException("SHA-256 is not available.", exception);
        }
    }
}
