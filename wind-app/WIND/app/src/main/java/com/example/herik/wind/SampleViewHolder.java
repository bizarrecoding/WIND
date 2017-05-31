package com.example.herik.wind;

import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.widget.TextView;

/**
 * Created by Herik on 5/5/2017.
 */

public class SampleViewHolder extends RecyclerView.ViewHolder {

    public TextView hora;
    public TextView speed;
    public TextView dir;

    public SampleViewHolder(View itemView) {
        super(itemView);
        hora = (TextView) itemView.findViewById(R.id.hora);
        speed = (TextView) itemView.findViewById(R.id.spd);
        dir = (TextView) itemView.findViewById(R.id.dir);
    }
    public TextView getDir() {
        return dir;
    }

    public TextView getHora() {
        return hora;
    }

    public TextView getSpeed() {
        return speed;
    }
}
