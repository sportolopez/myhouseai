import java.io.FileWriter;
import java.io.IOException;
import java.time.Duration;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import io.github.bonigarcia.wdm.WebDriverManager;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

public class WebScraper {
    
    public static void main(String[] args) {
        // Configura el WebDriverManager para Chrome
        WebDriverManager.chromedriver().setup();

        // Inicializa el WebDriver para Chrome
        WebDriver driver = new ChromeDriver();

         List<Map<String, String>> rows = new ArrayList<>();

        int pagina = 1;
        while (pagina<163) {
            try {
                // Navega a la URL deseada
                driver.get("https://cabaprop.com.ar/inmobiliarias?pagina="+String.valueOf(pagina));
                ++pagina;
                // Configura WebDriverWait
                WebDriverWait wait = new WebDriverWait(driver, Duration.ofSeconds(10));

                // Espera hasta que los elementos sean visibles
                
                List<WebElement> elementInmobiliaria = wait.until(ExpectedConditions.visibilityOfAllElementsLocatedBy(By.cssSelector("div.real-estate-content")));
                for (WebElement element : elementInmobiliaria) {
                    HashMap<String, String> datosInmo = new HashMap<>();
                    WebElement elementContent = element.findElement(By.className("real-estate-tc_content"));
                    String elementText = elementContent.findElement(By.tagName("h4")).getText();
                    datosInmo.put("Nombre", elementText);
                    List<WebElement> ul = elementContent.findElements(By.className("prop_details"));
                    List<WebElement> telefonos = ul.get(1).findElements(By.xpath(".//span"));

                    int index = 0;
                    for (WebElement webElement : telefonos) {
                        String email = webElement.getText(); 
                        if(email.equals("Whatsapp:"))
                            datosInmo.put("Whatsapp", telefonos.get(index+1).getText());
                        if(email.equals("Teléfono:"))
                            datosInmo.put("Teléfono", telefonos.get(index+1).getText());
                        if(email.equals("Correo:"))
                            datosInmo.put("Correo", telefonos.get(index+1).getText());
                        ++index;
                    }
                    /* 
                    WebElement footer = element.findElement(By.className("fp_footer_container"));
                    if(footer.getText().contains("venta")){
                        datosInmo.put("link_venta",footer.findElement(By.tagName("a")).getAttribute("href"));
                        datosInmo.put("venta", footer.getText().split(" ")[0]);
                    }
                        
                    if(footer.getText().contains("alquiler")){
                        datosInmo.put("link_alquiler",footer.findElement(By.tagName("a")).getAttribute("href"));
                        datosInmo.put("alquiler", footer.getText().split(" ")[0]);
                    }


                    Integer alquiler = Integer.parseInt(datosInmo.getOrDefault("alquiler","0"));
                    Integer venta = Integer.parseInt(datosInmo.getOrDefault("venta","0"));
                    */
                    rows.add(datosInmo);
                    for (String key : datosInmo.keySet()) {
                        String value = datosInmo.get(key);
                        System.out.print(key + ": " + value + " ");
                    }
                      




                    
                }

                
                // Recorre todos los elementos encontrados y obtiene el texto
                
            } catch (Exception e) {
                throw e;

            } finally {
                // Cierra el navegador
               
            }
        }
        writeMapsToCSV(rows,"inmo_pocas.csv");
    }

    public static void writeMapsToCSV(List<Map<String, String>> rows, String fileName) {
        if (rows.isEmpty()) {
            System.out.println("No hay datos para escribir en el CSV.");
            return;
        }

        try (FileWriter writer = new FileWriter(fileName)) {
            // Obtener el conjunto de claves del primer mapa para usar como cabeceras del CSV
            Map<String, String> headerMap = rows.get(0);
            String[] headers = headerMap.keySet().toArray(new String[0]);

            // Escribir las cabeceras del CSV
            writer.append(String.join(",", headers)).append('\n');

            // Escribir cada mapa (fila) en una línea separada
            for (Map<String, String> row : rows) {
                for (String header : headers) {
                    writer.append(row.getOrDefault(header, "")).append(',');
                }
                writer.append('\n');
            }

            System.out.println("Archivo CSV creado exitosamente: " + fileName);

        } catch (IOException e) {
            System.err.println("Error al escribir el archivo CSV: " + e.getMessage());
        }
    }
}
