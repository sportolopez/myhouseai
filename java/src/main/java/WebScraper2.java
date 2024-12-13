import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.select.Elements;

public class WebScraper2 {
    public static void main(String[] args) {
        try {
            System.out.println("Inicia");
            // Conectar a la página
            Document doc = Jsoup.connect("https://www.buscadorprop.com.ar/inmobiliarias/provincia-de-buenos-aires").get();

            // Extraer información de contacto (teléfono, email, WhatsApp)
            Elements inmobiliarias = doc.select(".inmobiliarias__ficha"); // Ajustar el selector CSS según la estructura de la página
            if (inmobiliarias.isEmpty()) {
                System.out.println("Vacio");
            }

            for (org.jsoup.nodes.Element inmobiliaria : inmobiliarias) {
                String nombre = inmobiliaria.select(".inmobiliarias__ficha__title").text(); // Ajusta el selector
                String telefono = inmobiliaria.select(".telefono").text(); // Ajusta el selector

                // Buscar un patrón de email (generalmente en forma de un link mailto)
                String email = inmobiliaria.select("a[href^=mailto]").attr("href").replace("mailto:", "");

                // Extraer enlace de WhatsApp
                String whatsappPhone = inmobiliaria.select("a[href*='api.whatsapp.com/send']").text();

                System.out.println("Nombre: " + nombre);
                System.out.println("Teléfono: " + telefono);
                System.out.println("Email: " + email);
                System.out.println("WhatsApp: " + whatsappPhone );
                System.out.println("-----------------------------------");
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
