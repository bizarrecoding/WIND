package com.example.herik.wind;

import android.content.Context;
import android.content.res.Resources;
import android.graphics.Color;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import java.util.ArrayList;

/**
 * Created by Herik on 6/5/2017.1
 */

public class SampleAdapter extends RecyclerView.Adapter<SampleViewHolder> {

    private ArrayList<Sample> samples;

    public SampleAdapter(ArrayList<Sample> samples){
        this.samples = samples;
    }

    @Override
    public SampleViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View sampleView = LayoutInflater.from(parent.getContext()).inflate(R.layout.samplerow,parent, false);
        return new SampleViewHolder(sampleView);
    }

    @Override
    public void onBindViewHolder(SampleViewHolder holder, int position) {
        Sample s = samples.get(position);
        TextView spd = holder.getSpeed();
        spd.setText(s.getSpeed());
        try {
            double speed  = Double.parseDouble(s.getSpeed());
            if(speed >= 50.0 && speed < 61.0){
                spd.setTextColor(0xfffdd835);
            }else if (speed >= 61.0 && speed < 89.0){
                spd.setTextColor(0xfffb8c00);
            }else if(speed >= 89.0){
                spd.setTextColor(0xffe53935);
            }else{
                spd.setTextColor(Color.WHITE);
            }
        }catch (Exception ex){
            spd.setTextColor(Color.WHITE);
        }
        holder.getDir().setText(s.getDirection());
        holder.getHora().setText(s.getTime());
    }

    @Override
    public int getItemCount() {
        return samples.size();
    }
}
