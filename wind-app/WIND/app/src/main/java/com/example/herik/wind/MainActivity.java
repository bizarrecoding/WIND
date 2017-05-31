package com.example.herik.wind;

import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.design.widget.TabLayout;
import android.support.v4.view.ViewPager;
import android.support.v7.app.AlertDialog;
import android.util.Log;
import android.view.Gravity;
import android.view.View;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;
import com.onesignal.OneSignal;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.Iterator;

public class MainActivity extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener{

    private TabLayout tabLayout;
    private ViewPager viewPager;
    private FloatingActionButton fab;
    protected Context ctx;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        ctx = this;
        fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                int tag = viewPager.getCurrentItem();
                if (tag>0){
                    subscribe(tag);
                }
            }
        });

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(
                this, drawer, toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        drawer.setDrawerListener(toggle);
        toggle.syncState();

        NavigationView navigationView = (NavigationView) findViewById(R.id.nav_view);
        navigationView.setNavigationItemSelectedListener(this);

        viewPager = (ViewPager) findViewById(R.id.viewpager);
        setupViewPager(viewPager);
        viewPager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {
                setNofityFab(position);
            }

            @Override
            public void onPageSelected(int position) {
                setNofityFab(position);
            }

            @Override
            public void onPageScrollStateChanged(int state) {

            }
        });

        OneSignal.idsAvailable(new OneSignal.IdsAvailableHandler() {
            @Override
            public void idsAvailable(String userId, String registrationId) {
                String text = "OneSignal UserID:\n" + userId + "\n\n";
                if (registrationId != null){
                    text += "Google Registration Id:\n" + registrationId;
                }else{
                    text += "Google Registration Id:\nCould not subscribe for push";
                }
                Log.d("OneSignal",text);
            }
        });

        tabLayout = (TabLayout) findViewById(R.id.tabs);
        tabLayout.setupWithViewPager(viewPager);

        OneSignal.startInit(this)
                .inFocusDisplaying(OneSignal.OSInFocusDisplayOption.Notification)
                .unsubscribeWhenNotificationsAreDisabled(true)
                .setNotificationReceivedHandler(new NotificationReceivedHandler())
                .init();
    }

    private void setNofityFab(final int position){
        if(position==0){
            fab.hide();
        }else {
            //fab.show();
            OneSignal.getTags(new OneSignal.GetTagsHandler() {
                @Override
                public void tagsAvailable(JSONObject tags) {
                    try {
                        if (tags != null){
                            Iterator keys = tags.keys();
                            Boolean sub = false;
                            while(keys.hasNext()) {
                                String key = (String)keys.next();
                                if(key.equals("Sensor"+position)){
                                    sub = true;
                                }
                            }
                            if(sub){
                                runOnUiThread(new Runnable() {
                                    @Override
                                    public void run() {
                                        fab.hide();
                                    }
                                });
                            }else{
                                runOnUiThread(new Runnable() {
                                    @Override
                                    public void run() {
                                        fab.show();
                                    }
                                });
                            }
                        }else{
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    fab.show();
                                }
                            });
                        }
                    }catch (Exception e){
                        Log.e("FAB",e.getMessage());
                    }
                }
            });
        }
    }

    private void setupViewPager(ViewPager viewPager) {
        ViewPagerAdapter adapter = new ViewPagerAdapter(getSupportFragmentManager());
        adapter.addFragment(new MainFragment(), "Inicio");
        adapter.addFragment(new OneFragment(), "Sensor 1");
        adapter.addFragment(new TwoFragment(), "Sensor 2");
        viewPager.setAdapter(adapter);
    }

    @Override
    public void onBackPressed() {
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        /*if (id == R.id.action_settings) {
            return true;
        }*/

        return super.onOptionsItemSelected(item);
    }

    @SuppressWarnings("StatementWithEmptyBody")
    @Override
    public boolean onNavigationItemSelected(MenuItem item) {
        // Handle navigation view item clicks here.
        int id = item.getItemId();
        if (id == R.id.mediciones) {
            viewPager.setCurrentItem(1);
        } else if (id == R.id.ubicacion) {
            viewPager.setCurrentItem(0);
        } else if (id == R.id.share) {

        } else if (id == R.id.notifications) {
            Intent i = new Intent(this,SettingsActivity.class);
            startActivity(i);
        }

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }

    public void subscribe(final int tag){
        // declaration
        AlertDialog.Builder adb = new AlertDialog.Builder(MainActivity.this,R.style.AppTheme);
        // modification
        adb.setTitle("Sensor #"+tag);
        adb.setMessage("Desea recibir notificaciones del sensor #"+tag);
        adb.setPositiveButton(android.R.string.ok, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {
                try {
                    JSONObject tags = new JSONObject();
                    if(tag==1){
                        tags.put("Sensor1", "1");
                    }else if (tag==2) {
                        tags.put("Sensor2", "2");
                    }else{
                        tags.put("Sensor0", "0");
                    }
                    OneSignal.sendTags(tags);
                    SharedPreferences sharedPref = getSharedPreferences("notifications",getApplicationContext().MODE_PRIVATE);
                    SharedPreferences.Editor editor = sharedPref.edit();
                    editor.putBoolean("notify",true);
                    editor.apply();
                    OneSignal.setSubscription(true);
                    Log.d("NOTIFICATIONS","subscription on");
                    fab.hide();
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        });

        //optional
        adb.setNegativeButton(android.R.string.cancel,null);
        //show
        AlertDialog ad = adb.create();
        ad.show();
    }

}

