package com.example.herik.wind;

import android.content.Context;
import android.content.SharedPreferences;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.widget.CompoundButton;
import android.widget.Switch;

import com.onesignal.OneSignal;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Iterator;

public class SettingsActivity extends AppCompatActivity {

    private Switch main, s1, s2;
    protected Context ctx;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);
        ctx = this;
        main = (Switch)findViewById(R.id.main);
        s1 = (Switch)findViewById(R.id.switch1);
        s1.setChecked(false);
        s1.setEnabled(false);
        s2 = (Switch)findViewById(R.id.switch2);
        s2.setChecked(false);
        s2.setEnabled(false);
        OneSignal.getTags(new OneSignal.GetTagsHandler() {
            @Override
            public void tagsAvailable(JSONObject tags) {
                try {
                    if (tags != null){
                        Iterator keys = tags.keys();
                        while(keys.hasNext()) {
                            String key = keys.next().toString();
                            Log.d("KEY",key+": "+tags.getString(key));
                            if(key.equals("Sensor1") ){
                                runOnUiThread(new Runnable() {
                                    @Override
                                    public void run() {
                                        s1.setChecked(true);
                                        Log.d("NOTIFICATIONS","(49)Sensor1: true");
                                    }
                                });
                            }
                            if (key.equals("Sensor2")){
                                runOnUiThread(new Runnable() {
                                    @Override
                                    public void run() {
                                        s2.setChecked(true);
                                        Log.d("NOTIFICATIONS","Sensor2: true");
                                    }
                                });
                            }
                        }
                    }else {
                        Log.d("NOTIFICATIONS","no tags avaliable");
                    }
                } catch (JSONException e) {
                    Log.e("",e.getMessage());
                } catch (Exception ex){
                    Log.e("",ex.getMessage());
                }
            }
        });
        SharedPreferences prefs = this.getSharedPreferences("notifications",getApplicationContext().MODE_PRIVATE);
        boolean notify = prefs.getBoolean("notify",false);
        main.setChecked(notify);
        s1.setEnabled(notify);
        s2.setEnabled(notify);
        OneSignal.setSubscription(notify);
        Log.d("NOTIFICATIONS","main: "+prefs.getBoolean("notify",false));

        main.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                SharedPreferences sharedPref = getSharedPreferences("notifications",getApplicationContext().MODE_PRIVATE);
                SharedPreferences.Editor editor = sharedPref.edit();
                if(main.isChecked()){
                    Log.d("NOTIFICATIONS","subscription on");
                    OneSignal.setSubscription(true);
                    editor.putBoolean("notify", true);
                    s1.setEnabled(true);
                    s2.setEnabled(true);
                }else{
                    Log.d("NOTIFICATIONS","subscription off");
                    editor.putBoolean("notify", false);
                    OneSignal.setSubscription(false);
                    s1.setEnabled(false);
                    s2.setEnabled(false);
                }
                editor.apply();
            }
        });

        s1.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                if(s1.isChecked()){
                    subscribe(1);
                    Log.d("NOTIFICATIONS","(108)Sensor1: true");
                }else{
                    OneSignal.deleteTag("Sensor1");
                }
            }
        });
        s2.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                if(s2.isChecked()){
                    Log.d("NOTIFICATIONS","Sensor2: true");
                    subscribe(2);
                }else{
                    OneSignal.deleteTag("Sensor2");
                }
            }
        });
    }

    private void subscribe(int tag){
        try {
            JSONObject tags = new JSONObject();
            if(tag==1){
                tags.put("Sensor1", "1");
                Log.d("NOTIFICATIONS","(132)Sensor1: true");
            }else if (tag==2) {
                tags.put("Sensor2", "2");
                Log.d("NOTIFICATIONS","(135)Sensor2: true");
            }else{
                tags.put("Sensor0", "0");
            }
            OneSignal.sendTags(tags);
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }
}
