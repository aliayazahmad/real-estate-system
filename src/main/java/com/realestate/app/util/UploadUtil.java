package com.realestate.app.util;

import javax.servlet.ServletContext;
import javax.servlet.http.Part;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.util.UUID;

public final class UploadUtil {
    private UploadUtil() {
    }

    public static String saveImage(Part part, ServletContext servletContext) throws IOException {
        if (part == null || part.getSize() == 0) {
            return null;
        }

        String contentType = part.getContentType();
        if (!"image/jpeg".equals(contentType) && !"image/png".equals(contentType) && !"image/webp".equals(contentType)) {
            throw new IOException("Only JPG, PNG, and WEBP images are allowed.");
        }

        String extension = contentType.equals("image/png") ? ".png" : contentType.equals("image/webp") ? ".webp" : ".jpg";
        String fileName = UUID.randomUUID().toString().replace("-", "") + extension;
        File uploadDirectory = new File(servletContext.getRealPath("/uploads"));
        if (!uploadDirectory.exists()) {
            Files.createDirectories(uploadDirectory.toPath());
        }
        part.write(new File(uploadDirectory, fileName).getAbsolutePath());
        return fileName;
    }

    public static void deleteImage(ServletContext servletContext, String imageName) {
        if (imageName == null || imageName.trim().isEmpty()) {
            return;
        }
        File file = new File(servletContext.getRealPath("/uploads"), imageName);
        if (file.exists()) {
            file.delete();
        }
    }
}
