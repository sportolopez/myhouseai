import java.io.IOException;
import java.nio.file.DirectoryStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;

public class ImageUploadGenerada {
    private static final String IMAGES_DIRECTORY = "C:\\Users\\sebap\\git\\inmo\\src\\main\\resources\\Sin_marca_reducida\\"; // Ruta al directorio con las imágenes
    private static final String JDBC_URL = "jdbc:mysql://localhost:3306/c1802222_myhouse";
    private static final String JDBC_USER = "c1802222_myhouse";
    private static final String JDBC_PASSWORD = "liPIzu81be";
    
    public static void main(String[] args) {
        try (Connection connection = DriverManager.getConnection(JDBC_URL, JDBC_USER, JDBC_PASSWORD)) {
            // Recorre el directorio de imágenes
            try (DirectoryStream<Path> directoryStream = Files.newDirectoryStream(Paths.get(IMAGES_DIRECTORY), "*.png")) {
                for (Path imagePath : directoryStream) {
                    String fileName = imagePath.getFileName().toString();
                    String id = fileName.substring(0, fileName.indexOf('.'));

                    byte[] imageBytes = Files.readAllBytes(imagePath);

                    if (imageBytes != null) {
                        saveImageToDatabase(connection, id, imageBytes);
                    }
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private static void saveImageToDatabase(Connection connection, String id, byte[] imageBytes) throws SQLException {
        String sql = "UPDATE inmobiliaria SET imagen_generada = ? WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setBytes(1, imageBytes);
            statement.setString(2, id);
            statement.executeUpdate();
        }
    }
}