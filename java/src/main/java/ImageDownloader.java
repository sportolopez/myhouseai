import java.io.BufferedReader;
import java.io.InputStream;
import java.net.URL;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

public class ImageDownloader {
    private static final String CSV_FILE_PATH = "C:\\Users\\sebap\\git\\inmo\\Inmobiliarias-imagen_orig.csv";
    private static final String JDBC_URL = "jdbc:mysql://localhost:3306/c1802222_myhouse";
    private static final String JDBC_USER = "c1802222_myhouse";
    private static final String JDBC_PASSWORD = "liPIzu81be";
    
    public static void main(String[] args) {
        Map<String, String> csvData = readCsv(CSV_FILE_PATH);

        try (Connection connection = DriverManager.getConnection(JDBC_URL, JDBC_USER, JDBC_PASSWORD)) {
            for (Map.Entry<String, String> entry : csvData.entrySet()) {
                String id = entry.getKey();
                String imageUrl = entry.getValue();

                if (imageUrl == null || imageUrl.isEmpty()) {
                    continue; // Si la URL está vacía, saltar a la siguiente fila
                }

                byte[] imageBytes = downloadImage(imageUrl);

                if (imageBytes != null) {
                    saveImageToDatabase(connection, id, imageBytes);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private static Map<String, String> readCsv(String filePath) {
        Map<String, String> data = new HashMap<>();
        try (BufferedReader reader = Files.newBufferedReader(Paths.get(filePath))) {
            String line = reader.readLine(); // Leer y descartar el encabezado
            while ((line = reader.readLine()) != null) {
                String[] parts = line.split(";");
                if (parts.length > 1) {
                    String id = parts[0].trim();
                    String imageUrl = parts[1].trim();
                    data.put(id, imageUrl);
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        return data;
    }

    private static byte[] downloadImage(String imageUrl) {
        try (InputStream in = new URL(imageUrl).openStream()) {
            return in.readAllBytes();
        } catch (Exception e) {

        	System.out.println(imageUrl);
            e.printStackTrace();
            return null;
        }
    }

    private static void saveImageToDatabase(Connection connection, String id, byte[] imageBytes) throws SQLException {
        String sql = "UPDATE inmobiliaria SET imagen_ejemplo = ? WHERE id = ?";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setBytes(1, imageBytes);
            statement.setString(2, id);
            statement.executeUpdate();
        }
    }
}
