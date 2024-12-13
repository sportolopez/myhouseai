import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URL;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

import com.fasterxml.jackson.databind.ObjectMapper;

public class SendEmailsAppVencidos {

    private static final String URL_IDS = "https://myhouseai.com/api/vencidos";
    private static final String BASE_URL_EMAIL = "https://myhouseai.com/api/send-emails";
    private static final String SUBJECT = "Mejoramos tus publicaciones con Inteligencia Artificial ¿Qué te parece?";
    private static final String TEMPLATE = "Email9Adjuntos";
    private static final int WAIT_TIME_MS = 30000; // 30 segundos
    private static final int MAX_RETRIES = 10; // Número máximo de reintentos

    public static void main(String[] args) {
        int attempt = 0;

        while (attempt < MAX_RETRIES) {
            try {
                // Paso 1: Obtener los IDs
                List<Integer> ids = fetchIds();
                if (ids != null && !ids.isEmpty()) {
                    // Paso 2: Enviar correos
                    sendEmails(ids);
                    break; // Salir del bucle si todo fue exitoso
                } else {
                    System.out.println("No se encontraron IDs para procesar.");
                }
            } catch (Exception e) {
                attempt++;
                try {
                    Thread.sleep(WAIT_TIME_MS*200);
                } catch (InterruptedException e1) {
                    // TODO Auto-generated catch block
                    e1.printStackTrace();
                }
                System.out.println("Error en el proceso completo. Reintentando desde el principio... Intento " + attempt + "/" + MAX_RETRIES);
                if (attempt == MAX_RETRIES) {
                    System.out.println("Se alcanzó el número máximo de reintentos. Abortando.");
                }
            }
        }
    }

    // Método para obtener los IDs desde la API
    private static List<Integer> fetchIds() throws IOException, InterruptedException {
        HttpClient client = HttpClient.newHttpClient();
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(URL_IDS))
                .GET()
                .build();

        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() == 200) {
            ObjectMapper objectMapper = new ObjectMapper();
            ResponseData responseData = objectMapper.readValue(response.body(), ResponseData.class);
            return Arrays.stream(responseData.getLista_ids().split(","))
                         .map(Integer::parseInt)
                         .collect(Collectors.toList());
        } else {
            throw new IOException("Error al obtener IDs. Código de estado: " + response.statusCode());
        }
    }

    // Método para enviar correos a la lista de IDs
    private static void sendEmails(List<Integer> ids) throws IOException, InterruptedException {
        for (Integer id : ids) {
            String apiUrl = String.format("%s?ids=%s&asunto=%s&template=%s&adjuntos=true",
                    BASE_URL_EMAIL, id, urlEncode(SUBJECT), urlEncode(TEMPLATE));

            int responseCode = sendGetRequest(apiUrl);

            // Si el envío falla, lanzar excepción para reiniciar el proceso desde el principio
            if (responseCode != HttpURLConnection.HTTP_OK) {
                throw new IOException("Error al enviar correo para ID " + id + ". Código de respuesta: " + responseCode);
            }

            System.out.println("Correo enviado exitosamente para ID: " + id);
            System.out.println("Esperando 20 segundos antes del siguiente envío...");
            Thread.sleep(WAIT_TIME_MS);
        }
    }

    // Método para realizar una solicitud GET y devolver el código de respuesta
    private static int sendGetRequest(String apiUrl) throws IOException {
        URL url = new URL(apiUrl);
        HttpURLConnection connection = (HttpURLConnection) url.openConnection();
        connection.setRequestMethod("GET");

        int responseCode = connection.getResponseCode();
        if (responseCode == HttpURLConnection.HTTP_OK) {
            System.out.println("Solicitud exitosa para la URL: " + apiUrl);
        } else {
            System.out.println("Error en la solicitud para la URL: " + apiUrl + ". Código de respuesta: " + responseCode);
        }
        return responseCode;
    }

    // Método para codificar la URL
    private static String urlEncode(String value) throws IOException {
        return java.net.URLEncoder.encode(value, "UTF-8");
    }

    // Clase para representar los datos de respuesta de la API
    static class ResponseData {
        private int cantidad_inmobiliarias_sin_email;
        private String lista_ids;

        // Getters y setters
        public int getCantidad_inmobiliarias_sin_email() {
            return cantidad_inmobiliarias_sin_email;
        }

        public void setCantidad_inmobiliarias_sin_email(int cantidad_inmobiliarias_sin_email) {
            this.cantidad_inmobiliarias_sin_email = cantidad_inmobiliarias_sin_email;
        }

        public String getLista_ids() {
            return lista_ids;
        }

        public void setLista_ids(String lista_ids) {
            this.lista_ids = lista_ids;
        }
    }
}
