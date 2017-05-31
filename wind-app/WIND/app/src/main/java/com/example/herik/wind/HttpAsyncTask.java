package com.example.herik.wind;

import android.os.AsyncTask;
import android.util.Log;
import android.view.View;
import android.widget.TextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.StringReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Random;

/**
 * Created by Herik on 5/5/2017.
 */

public class HttpAsyncTask extends AsyncTask<Void, Void, JSONObject> {

    private int sensor;
    private int request;
    protected View root;
    public final int FRONTINFO = 0;
    public final int SENSOR1 = 1;
    public final int SENSOR2 = 2;
    public final int TEST = 3;



    public HttpAsyncTask(int sensor, int request, View rootView) {
        this.sensor = sensor;
        this.request = request;
        this.root = rootView;
    }

    @Override
    protected JSONObject doInBackground(Void... params) {
        try {


            //URL url = new URL("http://ec2-54-91-250-124.compute-1.amazonaws.com/API/windapi.php");
            URL url = new URL(APIRequestStr(request,sensor));
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setConnectTimeout(15000);
            conn.setRequestMethod("GET");
            conn.setRequestProperty("Content-Type", "application/json");
            conn.setDoOutput(true);

            OutputStreamWriter wr = new OutputStreamWriter(conn.getOutputStream());
            //JSONObject json = APIRequestSetup(request);
            //wr.write(json.toString());
            wr.flush();

            InputStream in = conn.getInputStream();
            BufferedReader br = new BufferedReader(new InputStreamReader(in));
            String result = "";
            String resp = "";
            while ((result = br.readLine())!=null){
                resp = resp + result;
            }
            in.close();
            Log.d("RESPONSE",resp);
            //APIResponse(new JSONObject(resp),request);
            return new JSONObject(resp);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return null;
    }

    private String APIRequestStr(int requestType,int sensor){
        String url = "http://ec2-54-91-250-124.compute-1.amazonaws.com/API/windapi.php";
        switch (requestType) {
            case FRONTINFO:
                url+="?type=frontinfo&sensor="+sensor;
                break;
            case SENSOR1:
                url+="?type=sensorhistory&sensor="+sensor;
                break;
            case SENSOR2:
                url+="?type=sensorhistory&sensor="+sensor;
                break;
            case TEST:
                break;
        }
        return url;
    }
    private JSONObject APIRequestSetup(int requestType) {
        try {
            JSONObject info = new JSONObject();
            switch (requestType) {
                case FRONTINFO:
                    info.put("type", "frontinfo");
                    info.put("sensor", "1");
                    return info;
                case SENSOR1:
                    info.put("type", "sensorhistory");
                    info.put("sensor", "1");
                    return info;
                case SENSOR2:
                    info.put("type", "sensorhistory");
                    info.put("sensor", "2");
                    return info;
                case TEST:
                    return null;

            }
        } catch(JSONException e){
            e.printStackTrace();
        }
        return null;
    }
}
