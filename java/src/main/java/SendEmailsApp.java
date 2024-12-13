import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URL;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

import org.bouncycastle.asn1.ocsp.ResponseData;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;

public class SendEmailsApp {

    public static void main(String[] args) throws InterruptedException, IOException {
        List<Integer> ids = null;
        // Lista de IDs
        String url = "https://myhouseai.com/api/sinenvios";

        // Crear cliente HTTP
        HttpClient client = HttpClient.newHttpClient();

        // Crear la solicitud HTTP GET
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .GET()
                .build();

        // Enviar la solicitud y obtener la respuesta
        HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());

        // Verificar si la respuesta es exitosa (código 200)
        if (response.statusCode() == 200) {
            // Convertir la respuesta JSON en una lista de enteros
            ObjectMapper objectMapper = new ObjectMapper();
            ResponseData responseData = objectMapper.readValue(response.body(), ResponseData.class);
            ids = Arrays.asList(responseData.getLista_ids().split(","))
            .stream()
            .map(Integer::parseInt)
            .toList();
            

            // Imprimir la lista de IDs obtenida
            System.out.println("IDs obtenidos: " + ids);
        } else {
            System.out.println("Error al obtener los IDs. Código de estado: " + response.statusCode());
        }

        // Parámetros adicionales para la URL

        String asunto = "Mejoramos tus publicaciones con Inteligencia Artificial ¿Qué te parece?";
        String template = "Email9Adjuntos";
        int contador = 0;
        
        // Iterar sobre cada ID
        for (Integer id : ids) {
            try {
                // Construir la URL con el ID
                String apiUrl = String.format("https://myhouseai.com/api/send-emails?ids=%s&asunto=%s&template=%s&adjuntos=true",
                        id, urlEncode(asunto), urlEncode(template));

                // Hacer la solicitud HTTP y obtener el código de respuesta
                int responseCode = sendGetRequest(apiUrl);
                contador++;
                System.out.println("Solicitudes realizadas: " + contador);
                // Verificar el código de respuesta
                if (responseCode != HttpURLConnection.HTTP_OK) {
                    return;
                }

                // Pausar 20 segundos
                System.out.println("Esperando 20 segundos...");
                Thread.sleep(30000);

            } catch (IOException e) {
                System.out.println("Error enviando email para ID " + id + ": " + e.getMessage());
                break; // Detener el proceso en caso de una excepción
            }
        }
    }

    // Método para enviar una solicitud GET y devolver el código de respuesta
    private static int sendGetRequest(String apiUrl) throws IOException {
        URL url = new URL(apiUrl);
        HttpURLConnection connection = (HttpURLConnection) url.openConnection();
        connection.setRequestMethod("GET");
        
        int responseCode = connection.getResponseCode();
        if (responseCode == HttpURLConnection.HTTP_OK) {
            System.out.println("Solicitud exitosa para la URL: " + apiUrl);
        } else {
            System.out.println(connection.getResponseMessage());
            System.out.println("Error en la solicitud para la URL: " + apiUrl + ". Código de respuesta: " + responseCode);
        }
        return responseCode;
    }

    // Método para codificar la URL
    private static String urlEncode(String value) throws IOException {
        return java.net.URLEncoder.encode(value, "UTF-8");
    }

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
