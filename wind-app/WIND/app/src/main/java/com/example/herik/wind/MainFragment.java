package com.example.herik.wind;

import android.content.Context;
import android.os.Bundle;
import android.support.annotation.StringDef;
import android.support.design.widget.NavigationView;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;
import android.view.InflateException;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.LatLngBounds;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.concurrent.ExecutionException;


public class MainFragment extends Fragment implements OnMapReadyCallback {

    public View rootView;
    public MainFragment() {
        // Required empty public constructor
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        if (rootView != null) {
            ViewGroup parent = (ViewGroup) rootView.getParent();
            if (parent != null)
                parent.removeView(rootView);
        }
        try {
            // Inflate the layout for this fragment
            rootView = inflater.inflate(R.layout.fragment_main, container, false);
            SupportMapFragment mapFragment =
                    (SupportMapFragment) getChildFragmentManager().findFragmentById(R.id.map);
            mapFragment.getMapAsync(this);
            HttpAsyncTask task = new HttpAsyncTask(2,0,rootView);
            try {
                APIResponse(task.execute().get());
            } catch (InterruptedException e) {
                e.printStackTrace();
            } catch (ExecutionException e) {
                e.printStackTrace();
            }

        } catch (InflateException e) {
            /* map is already there, just return view as it is */
        }
        return rootView;
    }

    public void centerMap(GoogleMap gmap){
        LatLngBounds BARRANQUILLA = new LatLngBounds(
                new LatLng(10.975, -74.83), new LatLng(11.02, -74.7898));
        gmap.moveCamera(CameraUpdateFactory.newLatLngZoom(new LatLng(10.99915152, -74.81920903), 13));
        gmap.setLatLngBoundsForCameraTarget(BARRANQUILLA);
    }

    @Override
    public void onMapReady(GoogleMap gmap) {
        gmap.setMaxZoomPreference(17.5f);
        gmap.setMinZoomPreference(11.5f);
        gmap.getUiSettings().setZoomControlsEnabled(true);
        //gmap.getUiSettings().setMyLocationButtonEnabled(true);
        centerMap(gmap);
        gmap.addMarker(new MarkerOptions()
                .position(new LatLng(10.99130388, -74.82096638))
                .icon(BitmapDescriptorFactory.fromResource(R.drawable.sensor1))
                .title("Marker 1"));
        gmap.addMarker(new MarkerOptions()
                .position(new LatLng(11.00699916, -74.80561138))
                .icon(BitmapDescriptorFactory.fromResource(R.drawable.sensor2))
                .title("Marker 2"));

        gmap.setOnMarkerClickListener(new GoogleMap.OnMarkerClickListener() {
            @Override
            public boolean onMarkerClick(final Marker marker) {
                String title = marker.getTitle();
                Log.d("Marker Touched",title);
                try {
                    HttpAsyncTask task;
                    switch (title){
                        case "Marker 1":
                            task = new HttpAsyncTask(1,0,rootView);
                            APIResponse(task.execute().get());
                            break;
                        case "Marker 2":
                            task = new HttpAsyncTask(2,0,rootView);
                            APIResponse(task.execute().get());
                            break;
                    }
                } catch (InterruptedException e) {
                    e.printStackTrace();
                } catch (ExecutionException e) {
                    e.printStackTrace();
                }
                Log.d("Marker Touched","task exec");
                return true;
            }
        });
    }

    private void APIResponse(JSONObject response) {
        try {
            //TextView temp = (TextView)rootView.findViewById(R.id.temperature);
            //String temperature = response.getString("temp")+"°C";
            //temp.setText(temperature);
            TextView direction = (TextView) rootView.findViewById(R.id.direction);
            direction.setText(response.getString("dir"));
            TextView speed = (TextView)rootView.findViewById(R.id.speed);
            speed.setText(response.getString("vel"));
            TextView sensor = (TextView)rootView.findViewById(R.id.sensor);
            sensor.setText(response.getString("sensor"));
        } catch(JSONException e){
            e.printStackTrace();
        }
    }

    public void onAttach(Context context) {
        super.onAttach(context);
    }
    @Override
    public void onDetach() {
        super.onDetach();
    }
}
