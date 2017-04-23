/**
 * Created by zonghanchang on 4/22/17.
 */
import java.io.*;

import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;

import org.xml.sax.SAXException;

import java.util.regex.Pattern;

public class GenerateBig {
    public static void main(final String[] args) throws IOException,SAXException, TikaException {


        Metadata metadata = new Metadata();
        //FileInputStream inputstream = new FileInputStream(new File("/Users/zonghanchang/Documents/USC/course/CS572/pa4/NBCNewsData/NBCNewsDownloadData/fffcc48d-9bf7-4579-93c7-4e6d99d8984b.html"));


        Pattern p = Pattern.compile("\\s+");
        File dir = new File("/Users/zonghanchang/Documents/USC/course/CS572/pa4/NBCNewsData/NBCNewsDownloadData");

        try {
            for(File file : dir.listFiles()){
                FileInputStream inputstream = new FileInputStream(file);
                BodyContentHandler handler = new BodyContentHandler(-1);
                HtmlParser htmlparser = new HtmlParser();
                ParseContext pcontext = new ParseContext();
                htmlparser.parse(inputstream, handler, metadata,pcontext);
                String[] tokens = p.split(handler.toString());
                StringBuilder sb = new StringBuilder();
                for(String token : tokens){
                    sb.append(token).append(" ");
                }

                File out = new File("big.txt");
                if(!out.exists()) {
                    out.createNewFile();
                }
                BufferedWriter bw = new BufferedWriter(new FileWriter(out, true));
                bw.write(sb.toString());
                bw.flush();
                bw.close();
            }
        } catch (IOException e) {

        }
    }
}
