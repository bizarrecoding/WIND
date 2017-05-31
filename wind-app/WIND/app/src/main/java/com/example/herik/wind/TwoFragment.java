package com.example.herik.wind;


import android.content.Context;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.jjoe64.graphview.DefaultLabelFormatter;
import com.jjoe64.graphview.GraphView;
import com.jjoe64.graphview.LegendRenderer;
import com.jjoe64.graphview.helper.DateAsXAxisLabelFormatter;
import com.jjoe64.graphview.helper.StaticLabelsFormatter;
import com.jjoe64.graphview.series.DataPoint;
import com.jjoe64.graphview.series.LineGraphSeries;

import org.json.JSONException;
import org.json.JSONObject;

import java.text.NumberFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Iterator;
import java.util.concurrent.ExecutionException;


/**
 * A simple {@link Fragment} subclass.
 */
public class TwoFragment extends Fragment {

    private View rootView;
    private RecyclerView sampleList;
    private ArrayList<Sample> Samples;
    private SwipeRefreshLayout srl;
    private final static int INTERVAL = 1000 * 60 * 10; //10 minutes

    public TwoFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        Samples = new ArrayList<>();
        rootView = inflater.inflate(R.layout.fragment_two, container, false);
        startTask(0);
        srl = (SwipeRefreshLayout) rootView.findViewById(R.id.swiperefresh);
        srl.setOnRefreshListener(
                new SwipeRefreshLayout.OnRefreshListener() {
                    @Override
                    public void onRefresh() {
                        Log.i("UPDATE", "onRefresh called from SwipeRefreshLayout");
                        updateS2(1);
                    }
                }
        );
        final Handler mHandler = new Handler();
        mHandler.postDelayed( new Runnable(){
            @Override
            public void run() {
                updateS2(0);
                mHandler.postDelayed(this,INTERVAL);
            }
        },INTERVAL);
        return rootView;
    }
    protected void updateS2(int refresh){
        GraphView graph = (GraphView) rootView.findViewById(R.id.graph2);
        graph.removeAllSeries();
        Samples = new ArrayList<>();
        startTask(refresh);
    }

    private void startTask(int update){
        HttpAsyncTask task = new HttpAsyncTask(2,2,rootView);
        try {
            APIResponse(task.execute().get());
            drawGraph();
            sampleList = (RecyclerView) rootView.findViewById(R.id.sampleList1);
            sampleList.setLayoutManager(new LinearLayoutManager(getActivity()));
            sampleList.setAdapter(new SampleAdapter(Samples));
            sampleList.setLayoutManager(new LinearLayoutManager(this.getContext()));
        } catch (InterruptedException e) {
            e.printStackTrace();
        } catch (ExecutionException e) {
            e.printStackTrace();
        }
        if (update==1){
            srl.setRefreshing(false);
        }
    }
    private void drawGraph() {
        GraphView graph = (GraphView) rootView.findViewById(R.id.graph2);

        LineGraphSeries<DataPoint> series = new LineGraphSeries<DataPoint>();
        ArrayList<String> times = new ArrayList<>();
        for (int i = Samples.size()-1; i>=0;i--) {
            Sample s = Samples.get(i);
            times.add(s.getTime().substring(11,16));
            DataPoint dp = new DataPoint((double)(7-i),Double.parseDouble(s.getSpeed()));
            series.appendData(dp,false,10);
        }
        series.setDrawDataPoints(true);
        series.setTitle("Vel");
        series.setBackgroundColor(R.color.white);
        graph.addSeries(series);

        series.setDrawDataPoints(true);
        graph.getLegendRenderer().setVisible(true);
        graph.getLegendRenderer().setAlign(LegendRenderer.LegendAlign.TOP);

        graph.getGridLabelRenderer().setLabelFormatter(new DefaultLabelFormatter() {
            @Override
            public String formatLabel(double value, boolean isValueX) {
                if (isValueX) {
                    // show normal x values
                    return super.formatLabel(value, isValueX);
                } else {
                    String val = value+"";
                    int point = val.indexOf(".");
                    if(point>0){
                        if((point+2)<(val.length()-1)){
                            val = val.substring(0,point+2);
                        }
                    }
                    value= Double.parseDouble(val);
                    return super.formatLabel(value, isValueX);
                }
            }
        });

        graph.getLegendRenderer().setBackgroundColor(R.color.white);
        graph.getGridLabelRenderer().setNumVerticalLabels(4);

        StaticLabelsFormatter staticLabelsFormatter = new StaticLabelsFormatter(graph);
        staticLabelsFormatter.setHorizontalLabels(new String[] {"", times.get(1), times.get(2),times.get(3),times.get(4), times.get(5),""});
        graph.getGridLabelRenderer().setLabelFormatter(staticLabelsFormatter);
        graph.getGridLabelRenderer().setHumanRounding(false);

    }

    private void APIResponse(JSONObject jsonObject) {
        try {
            JSONObject history = jsonObject.getJSONObject("history");
            for (Iterator<String> j = history.keys(); j.hasNext();) {
                String key = j.next();
                JSONObject sample = history.getJSONObject(key);
                String vel = sample.getString("vel");
                String dir = sample.getString("dir");
                String hora = sample.getString("hora");
                Samples.add(new Sample(vel,dir,hora));
            }
        } catch (JSONException e) {
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
